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
     * Create a new Donation Campaign (supports target_mohalla scoping)
     */
    public function store(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'name' => 'required|string|max:255',
            'category' => 'required|in:zameen,mard,nikah,by_choice',
            'target_mohalla' => 'nullable|string|max:255',
            'mohalla_id' => 'nullable|exists:mohallas,id',
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

        // Resolve target mohalla name
        $targetMohalla = $request->target_mohalla ?: 'All';
        if ($request->filled('mohalla_id')) {
            $mohallaRecord = Mohalla::find($request->mohalla_id);
            if ($mohallaRecord) {
                $targetMohalla = $mohallaRecord->name;
            }
        }

        DB::beginTransaction();
        try {
            $campaign = DonationCampaign::create([
                'masjid_id' => $request->masjid_id,
                'madarsa_id' => $request->madarsa_id,
                'created_by' => $user->id,
                'name' => $request->name,
                'category' => $request->category,
                'target_mohalla' => $targetMohalla,
                'rate_per_unit' => $request->rate_per_unit,
                'target_amount' => $request->target_amount,
                'description' => $request->description,
                'status' => 'active',
            ]);

            // Auto-initialize ledger entries for donors (scoped to target_mohalla if not 'All')
            $donorsQuery = User::query();
            if ($request->filled('masjid_id')) {
                $donorsQuery->where('selected_masjid_id', $request->masjid_id);
            } else {
                $donorsQuery->where('selected_madarsa_id', $request->madarsa_id);
            }

            if (!empty($targetMohalla) && strtolower($targetMohalla) !== 'all') {
                $donorsQuery->where('mohalla', $targetMohalla);
            }

            $donors = $donorsQuery->get();
            $unitRate = (float)($request->rate_per_unit ?? 0);

            foreach ($donors as $donor) {
                $calcAmount = match ($request->category) {
                    'mard', 'zameen', 'nikah' => $unitRate,
                    default => 0.00,
                };

                DonationLedger::create([
                    'campaign_id' => $campaign->id,
                    'masjid_id' => $request->masjid_id,
                    'madarsa_id' => $request->madarsa_id,
                    'donor_user_id' => $donor->id,
                    'mohalla' => $donor->mohalla ?? 'General',
                    'unit_count' => 1.00,
                    'calculated_amount' => $calcAmount,
                    'paid_amount' => 0.00,
                    'balance' => $calcAmount,
                    'payment_status' => 'unpaid',
                ]);
            }

            DB::commit();

            // Broadcast FCM push notification to targeted donors
            if ($donors->isNotEmpty()) {
                try {
                    $title = "Naya Chanda Campaign: {$campaign->name}";
                    $body = "{$placeName} me naya campaign shuru hua hai. Detail app me dekhein.";
                    $data = [
                        'type' => 'new_campaign',
                        'campaign_id' => (string)$campaign->id,
                        'place_name' => (string)$placeName,
                        'target_mohalla' => (string)$targetMohalla,
                    ];

                    $this->firebase->sendToUsers($donors, $title, $body, $data);
                } catch (\Throwable $fcmError) {
                    Log::error("Campaign FCM Notification Error: " . $fcmError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully and notifications dispatched.',
                'data' => $campaign->load('ledgers'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to create campaign: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
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
}
