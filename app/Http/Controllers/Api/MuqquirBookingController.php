<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MuqquirBooking;
use App\Models\MuqquirProfile;
use App\Models\MuqquirAvailability;
use App\Models\User;
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
     * Send a booking request to a muqquir
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'muqquir_profile_id' => 'required|exists:muqquir_profiles,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $muqquirProfile = MuqquirProfile::with('user')->findOrFail($request->muqquir_profile_id);

        if ($muqquirProfile->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Muqquir profile is not approved'
            ], 400);
        }

        // Check availability (not unavailable and not booked)
        $isUnavailable = MuqquirAvailability::where('muqquir_profile_id', $muqquirProfile->id)
            ->where('available_date', $request->booking_date)
            ->whereIn('status', ['unavailable', 'booked'])
            ->exists();

        if ($isUnavailable) {
            return response()->json([
                'success' => false,
                'message' => 'Muqquir is not available on this date'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $booking = MuqquirBooking::create([
                'user_id' => auth()->id(),
                'muqquir_profile_id' => $request->muqquir_profile_id,
                'booking_date' => $request->booking_date,
                'travel_fee' => $muqquirProfile->travel_fee,
                'details' => $request->details,
                'status' => 'pending',
            ]);

            DB::commit();

            // Send Notification to Muqquir
            $member = auth()->user();
            $title = 'New Booking Request';
            $body = "{$member->name} has requested a booking on {$request->booking_date}.";
            
            $this->firebase->sendToUser(
                $muqquirProfile->user,
                $title,
                $body,
                [
                    'type' => 'muqquir_booking_request',
                    'booking_id' => (string) $booking->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking request sent successfully',
                'data' => $booking
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send booking request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bookings received by the muqquir (for Muqquir)
     */
    public function receivedBookings(): JsonResponse
    {
        $profile = auth()->user()->muqquirProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Muqquir profile not found'
            ], 404);
        }

        $bookings = MuqquirBooking::with('user:id,name,phone,address')
            ->where('muqquir_profile_id', $profile->id)
            ->orderBy('booking_date', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Received bookings fetched successfully',
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ]
        ]);
    }

    /**
     * Get bookings made by the user (for Member)
     */
    public function myBookings(): JsonResponse
    {
        $bookings = MuqquirBooking::with(['muqquirProfile.user:id,name,phone'])
            ->where('user_id', auth()->id())
            ->orderBy('booking_date', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'My bookings fetched successfully',
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ]
        ]);
    }

    /**
     * Accept or Reject booking (for Muqquir)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $profile = auth()->user()->muqquirProfile;
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Muqquir profile not found'
            ], 404);
        }

        $booking = MuqquirBooking::where('id', $id)
            ->where('muqquir_profile_id', $profile->id)
            ->firstOrFail();

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already ' . $booking->status
            ], 400);
        }

        try {
            DB::beginTransaction();

            $booking->update(['status' => $request->status]);

            if ($request->status === 'accepted') {
                // Mark date as booked in availability
                MuqquirAvailability::updateOrCreate(
                    [
                        'muqquir_profile_id' => $profile->id,
                        'available_date' => $booking->booking_date
                    ],
                    [
                        'status' => 'booked'
                    ]
                );
            }

            DB::commit();

            // Send Notification to Member
            $muqquirName = auth()->user()->name;
            $title = "Booking Request {$request->status}";
            $body = "Muqquir {$muqquirName} has {$request->status} your booking for {$booking->booking_date->format('Y-m-d')}.";
            
            $this->firebase->sendToUser(
                $booking->user,
                $title,
                $body,
                [
                    'type' => 'muqquir_booking_status',
                    'booking_id' => (string) $booking->id,
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Booking {$request->status} successfully",
                'data' => $booking
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking status: ' . $e->getMessage()
            ], 500);
        }
    }
}
