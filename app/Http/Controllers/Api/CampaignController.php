<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Create a new Donation Campaign (applies globally across all Mohallas with Unit Price)
     */
    public function store(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'name' => 'required|string|max:255',
            'category' => 'required|in:zameen,mard,nikah,by_choice',
            'unit_price' => 'nullable|numeric|min:0',
            'rate_per_unit' => 'nullable|numeric|min:0',
            'target_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json(['message' => 'Please provide either masjid_id or madarsa_id.'], 422);
        }

        $user = $request->user();
        $placeName = '';

        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            $isMutvalli = ($masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->masjid_id == $masjid->id || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized. Only the Masjid Mutvalli can create campaigns.'], 403);
            }
            $placeName = $masjid->name;
        } else {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            $isMutvalli = ($madarsa->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->madarsa_id == $madarsa->id || ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id == $madarsa->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized. Only the Madarsa Admin can create campaigns.'], 403);
            }
            $placeName = $madarsa->name;
        }

        // Resolve unit price (accepts either unit_price or rate_per_unit)
        $unitPrice = $request->input('unit_price') ?? $request->input('rate_per_unit');
        if ($request->category !== 'by_choice' && ($unitPrice === null || (float)$unitPrice <= 0)) {
            return response()->json([
                'message' => 'Unit price is required for unit-based campaigns.',
                'errors' => ['unit_price' => ['The unit price field is required when category is not by_choice.']],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $campaign = DonationCampaign::create([
                'masjid_id' => $request->masjid_id,
                'madarsa_id' => $request->madarsa_id,
                'created_by' => $user->id,
                'name' => $request->name,
                'category' => $request->category,
                'target_mohalla' => null, // Explicitly NULL = All Mohallas
                'unit_price' => $unitPrice,
                'rate_per_unit' => $unitPrice, // Keep in sync for backward compatibility
                'target_amount' => $request->target_amount,
                'description' => $request->description,
                'status' => 'active',
            ]);

            // Auto-initialize ledger entries for all approved donors across ALL Mohallas
            $donorsQuery = User::query()
                ->where(function ($query) use ($request) {
                    if ($request->filled('masjid_id')) {
                        $query->where('selected_masjid_id', $request->masjid_id)
                            ->orWhereHas('donorRequests', function ($sub) use ($request) {
                                $sub->where('masjid_id', $request->masjid_id)->where('status', 'approved');
                            });
                    } else {
                        $query->where('selected_madarsa_id', $request->madarsa_id)
                            ->orWhereHas('donorRequests', function ($sub) use ($request) {
                                $sub->where('madarsa_id', $request->madarsa_id)->where('status', 'approved');
                            });
                    }
                })
                ->with(['donorRequests' => function ($q) use ($request) {
                    if ($request->filled('masjid_id')) {
                        $q->where('masjid_id', $request->masjid_id)->where('status', 'approved')->latest();
                    } else {
                        $q->where('madarsa_id', $request->madarsa_id)->where('status', 'approved')->latest();
                    }
                }]);

            $donors = $donorsQuery->get();
            $unitRate = (float)($unitPrice ?? 0);

            foreach ($donors as $donor) {
                $donorReq = $donor->donorRequests->first();

                // Retrieve Donor's Registered Family Unit Count (defaults to 1 if not set)
                $unitCount = 1;
                if (!empty($donor->unit_count) && (int)$donor->unit_count > 0) {
                    $unitCount = (int)$donor->unit_count;
                } elseif ($donorReq && !empty($donorReq->unit_count) && (int)$donorReq->unit_count > 0) {
                    $unitCount = (int)$donorReq->unit_count;
                }

                // Resolve donor's mohalla name for ledger categorization
                $mohallaName = $donor->mohalla ?? ($donorReq ? $donorReq->mohalla : null) ?? 'General';

                // Auto-Ledger Calculation: unit_price * donor.unit_count
                $calcAmount = 0.00;
                if ($request->category !== 'by_choice' && $unitRate > 0) {
                    $calcAmount = round($unitRate * $unitCount, 2);
                }

                DonationLedger::create([
                    'campaign_id' => $campaign->id,
                    'masjid_id' => $request->masjid_id,
                    'madarsa_id' => $request->madarsa_id,
                    'donor_user_id' => $donor->id,
                    'mohalla' => $mohallaName,
                    'unit_count' => $unitCount,
                    'calculated_amount' => $calcAmount,
                    'paid_amount' => 0.00,
                    'balance' => $calcAmount,
                    'payment_status' => $calcAmount > 0 ? 'unpaid' : 'paid',
                    'recorded_by_user_id' => $user->id,
                    'notes' => "Auto pledge: {$unitCount} unit(s) @ ₹{$unitRate}/unit",
                ]);
            }

            DB::commit();

            // Broadcast FCM push notification to all donors across all mohallas
            if ($donors->isNotEmpty()) {
                try {
                    $title = "Naya Chanda Campaign: {$campaign->name}";
                    $body = ($unitRate > 0)
                        ? "{$placeName} me naya campaign shuru hua hai (₹{$unitRate}/unit). Detail app me dekhein."
                        : "{$placeName} me naya campaign shuru hua hai. Detail app me dekhein.";
                    $data = [
                        'type' => 'new_campaign',
                        'campaign_id' => (string)$campaign->id,
                        'place_name' => (string)$placeName,
                        'unit_price' => (string)$unitRate,
                    ];

                    $this->firebase->sendToUsers($donors, $title, $body, $data);
                } catch (\Throwable $fcmError) {
                    Log::error("Campaign FCM Notification Error: " . $fcmError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaign created for all Mohallas and member ledgers initialized successfully.',
                'data' => $campaign->load('ledgers'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to create campaign: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List campaigns for a Masjid or Madarsa
     */
    public function index(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'status' => 'nullable|in:active,completed,archived',
            'target_mohalla' => 'nullable|string',
        ]);

        $query = DonationCampaign::query();

        if ($request->filled('masjid_id')) {
            $query->where('masjid_id', $request->masjid_id);
        } elseif ($request->filled('madarsa_id')) {
            $query->where('madarsa_id', $request->madarsa_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('target_mohalla') && strtolower($request->target_mohalla) !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('target_mohalla', $request->target_mohalla)
                  ->orWhere('target_mohalla', 'All')
                  ->orWhereNull('target_mohalla');
            });
        }

        $campaigns = $query->withCount('ledgers')
            ->withSum('ledgers as total_calculated', 'calculated_amount')
            ->withSum('ledgers as total_collected', 'paid_amount')
            ->withSum('ledgers as total_balance', 'balance')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * Show single campaign details
     */
    public function show($id)
    {
        $campaign = DonationCampaign::with(['masjid', 'madarsa', 'creator:id,name,phone'])
            ->withCount('ledgers')
            ->withSum('ledgers as total_calculated', 'calculated_amount')
            ->withSum('ledgers as total_collected', 'paid_amount')
            ->withSum('ledgers as total_balance', 'balance')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $campaign,
        ]);
    }

    /**
     * Update Campaign Status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,completed,archived',
        ]);

        $campaign = DonationCampaign::findOrFail($id);
        $user = $request->user();

        $isOwner = false;
        if ($campaign->masjid_id) {
            $isOwner = ($campaign->masjid->user_id === $user->id) || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists();
        } elseif ($campaign->madarsa_id) {
            $isOwner = ($campaign->madarsa->user_id === $user->id) || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists();
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $campaign->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign status updated successfully.',
            'data' => $campaign,
        ]);
    }

    /**
     * Update Campaign Details
     */
    public function update(Request $request, $id)
    {
        $campaign = DonationCampaign::with(['masjid', 'madarsa'])->findOrFail($id);
        $user = $request->user();

        $isOwner = false;
        if ($campaign->masjid_id) {
            $isOwner = ($campaign->masjid && $campaign->masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($campaign->created_by === $user->id);
        } elseif ($campaign->madarsa_id) {
            $isOwner = ($campaign->madarsa && $campaign->madarsa->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($campaign->created_by === $user->id);
        }

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to edit this campaign.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|in:zameen,mard,nikah,by_choice',
            'unit_price' => 'nullable|numeric|min:0',
            'rate_per_unit' => 'nullable|numeric|min:0',
            'target_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,completed,archived',
        ]);

        $unitPrice = $request->input('unit_price') ?? $request->input('rate_per_unit') ?? $campaign->unit_price;

        $campaignUpdates = [
            'name' => $request->input('name', $campaign->name),
            'category' => $request->input('category', $campaign->category),
            'unit_price' => $unitPrice,
            'rate_per_unit' => $unitPrice,
            'target_mohalla' => null, // Stays global for all mohallas
        ];

        if ($request->has('target_amount')) {
            $campaignUpdates['target_amount'] = $request->target_amount;
        }
        if ($request->has('description')) {
            $campaignUpdates['description'] = $request->description;
        }
        if ($request->has('status')) {
            $campaignUpdates['status'] = $request->status;
        }

        $campaign->update($campaignUpdates);

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data' => $campaign->fresh(),
        ]);
    }

    /**
     * Delete Campaign
     */
    public function destroy(Request $request, $id)
    {
        $campaign = DonationCampaign::with(['masjid', 'madarsa'])->findOrFail($id);
        $user = $request->user();

        $isOwner = false;
        if ($campaign->masjid_id) {
            $isOwner = ($campaign->masjid && $campaign->masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($campaign->created_by === $user->id);
        } elseif ($campaign->madarsa_id) {
            $isOwner = ($campaign->madarsa && $campaign->madarsa->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($campaign->created_by === $user->id);
        }

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this campaign.'], 403);
        }

        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.',
        ]);
    }
}
