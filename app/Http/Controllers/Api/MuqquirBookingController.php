<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MuqquirBooking;
use App\Models\MuqquirProfile;
use App\Models\MuqquirAvailability;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MuqquirBookingController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * 1. Send a booking request (Member)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'muqquir_profile_id' => 'required|exists:muqquir_profiles,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $muqquirProfile = MuqquirProfile::with('user')->findOrFail($request->muqquir_profile_id);

        if ($muqquirProfile->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Muqquir profile is not approved'], 400);
        }

        $isUnavailable = MuqquirAvailability::where('muqquir_profile_id', $muqquirProfile->id)
            ->where('available_date', $request->booking_date)
            ->whereIn('status', ['unavailable', 'booked'])
            ->exists();

        if ($isUnavailable) {
            return response()->json(['success' => false, 'message' => 'Muqquir is not available on this date'], 400);
        }

        try {
            DB::beginTransaction();
            $booking = MuqquirBooking::create([
                'user_id' => auth()->id(),
                'muqquir_profile_id' => $request->muqquir_profile_id,
                'booking_date' => $request->booking_date,
                'details' => $request->details,
                'status' => 'pending',
            ]);
            DB::commit();

            $this->firebase->sendToUser(
                $muqquirProfile->user,
                'New Booking Request',
                auth()->user()->name . " has requested a booking for {$request->booking_date}.",
                ['type' => 'muqquir_booking_request', 'booking_id' => (string)$booking->id]
            );

            return response()->json(['success' => true, 'message' => 'Request sent to Muqquir', 'data' => $booking]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 2. Muqquir proposes a travel fee
     */
    public function proposeFee(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'travel_fee' => 'required|numeric|min:0',
        ]);

        $profile = auth()->user()->muqquirProfile;
        $booking = MuqquirBooking::where('id', $id)->where('muqquir_profile_id', $profile->id)->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Can only propose fee for pending requests'], 400);
        }

        $booking->update([
            'travel_fee' => $request->travel_fee,
            'status' => 'fee_proposed'
        ]);

        $this->firebase->sendToUser(
            $booking->user,
            'Travel Fee Proposed',
            "Muqquir " . auth()->user()->name . " has proposed a travel fee of {$request->travel_fee}.",
            ['type' => 'muqquir_fee_proposed', 'booking_id' => (string)$booking->id, 'fee' => (string)$request->travel_fee]
        );

        return response()->json(['success' => true, 'message' => 'Fee proposed to member', 'data' => $booking]);
    }

    /**
     * 3. Member accepts the fee and confirms booking
     */
    public function acceptBooking(int $id): JsonResponse
    {
        $booking = MuqquirBooking::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($booking->status !== 'fee_proposed') {
            return response()->json(['success' => false, 'message' => 'No fee proposal found to accept'], 400);
        }

        try {
            DB::beginTransaction();
            $booking->update(['status' => 'accepted']);

            // Mark date as booked
            MuqquirAvailability::updateOrCreate(
                ['muqquir_profile_id' => $booking->muqquir_profile_id, 'available_date' => $booking->booking_date],
                ['status' => 'booked']
            );
            DB::commit();

            $this->firebase->sendToUser(
                $booking->muqquirProfile->user,
                'Booking Confirmed!',
                auth()->user()->name . " has accepted the fee and confirmed the booking.",
                ['type' => 'muqquir_booking_accepted', 'booking_id' => (string)$booking->id]
            );

            return response()->json(['success' => true, 'message' => 'Booking confirmed successfully', 'data' => $booking]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject booking (either Muqquir or Member)
     */
    public function rejectBooking(int $id): JsonResponse
    {
        $user = auth()->user();
        $booking = MuqquirBooking::findOrFail($id);

        // Check if user is the member OR the muqquir
        $isMuqquir = ($user->muqquirProfile && $booking->muqquir_profile_id === $user->muqquirProfile->id);
        $isMember = ($booking->user_id === $user->id);

        if (!$isMuqquir && !$isMember) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $booking->update(['status' => 'rejected']);

        // Notify the other party
        $recipient = $isMuqquir ? $booking->user : $booking->muqquirProfile->user;
        $this->firebase->sendToUser(
            $recipient,
            'Booking Rejected',
            "The booking for {$booking->booking_date->format('Y-m-d')} has been rejected.",
            ['type' => 'muqquir_booking_rejected', 'booking_id' => (string)$booking->id]
        );

        return response()->json(['success' => true, 'message' => 'Booking rejected']);
    }

    public function receivedBookings(): JsonResponse
    {
        $profile = auth()->user()->muqquirProfile;
        if (!$profile) return response()->json(['success' => false, 'message' => 'Muqquir profile not found'], 404);

        $bookings = MuqquirBooking::with('user:id,name,phone,address')
            ->where('muqquir_profile_id', $profile->id)
            ->orderBy('booking_date', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function myBookings(): JsonResponse
    {
        $bookings = MuqquirBooking::with(['muqquirProfile.user:id,name,phone'])
            ->where('user_id', auth()->id())
            ->orderBy('booking_date', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $bookings]);
    }
}
