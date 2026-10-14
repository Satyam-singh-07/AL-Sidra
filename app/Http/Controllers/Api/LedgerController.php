<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Masjid;
use App\Models\Madarsa;
use App\Models\MohallaMutawalli;
use App\Models\MasjidBroadcastSetting;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LedgerController extends Controller
{
    private function checkIsMutvalli($user, $masjidId = null, $madarsaId = null)
    {
        $hasMutvalliRole = $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists();

        $isCategoryMutvalli = false;
        if ($user->memberProfile && $user->memberProfile->category) {
            $catName = strtolower($user->memberProfile->category->name ?? '');
            if (str_contains($catName, 'mutvalli') || str_contains($catName, 'mutawalli') || str_contains($catName, 'admin')) {
                $isCategoryMutvalli = true;
            }
        }

        $isPlaceOwner = false;
        if ($masjidId && Masjid::where('id', $masjidId)->where('user_id', $user->id)->exists()) {
            $isPlaceOwner = true;
        } elseif ($madarsaId && Madarsa::where('id', $madarsaId)->where('user_id', $user->id)->exists()) {
            $isPlaceOwner = true;
        }

        return ($isPlaceOwner || $hasMutvalliRole || $isCategoryMutvalli);
    }

    /**
     * Get Ledger entries with privacy scoping
     */
    public function index(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'mohalla' => 'nullable|string',
            'payment_status' => 'nullable|in:paid,partial,unpaid',
            'search' => 'nullable|string',
        ]);

        $user = $request->user()->load(['roles', 'memberProfile.category']);
        $campaign = DonationCampaign::findOrFail($request->campaign_id);
        $masjidId = $campaign->masjid_id;
        $madarsaId = $campaign->madarsa_id;

        $isMutvalli = $this->checkIsMutvalli($user, $masjidId, $madarsaId);

        $mohallaMutvalli = null;
        if ($masjidId) {
            $mohallaMutvalli = MohallaMutawalli::where('masjid_id', $masjidId)->where('user_id', $user->id)->first();
        } elseif ($madarsaId) {
            $mohallaMutvalli = MohallaMutawalli::where('madarsa_id', $madarsaId)->where('user_id', $user->id)->first();
        }

        $broadcast = null;
        if ($masjidId) {
            $broadcast = MasjidBroadcastSetting::where('masjid_id', $masjidId)->first();
        } elseif ($madarsaId) {
            $broadcast = MasjidBroadcastSetting::where('madarsa_id', $madarsaId)->first();
        }

        $isPublic = $broadcast ? $broadcast->isCurrentlyPublic() : false;
        $publicUntil = $broadcast && $isPublic ? $broadcast->public_until : null;
        $daysRemaining = $broadcast && $isPublic ? $broadcast->days_remaining : 0;

        // Base query
        $query = DonationLedger::with([
            'donor:id,name,phone,profile_picture,mohalla',
            'campaign:id,name,category,rate_per_unit',
            'recorder:id,name',
        ])->where('campaign_id', $campaign->id);

        // RBAC Scoping
        if ($isMutvalli) {
            // Mutvalli Access: view all & filter by mohalla
            if ($request->filled('mohalla')) {
                $query->where('mohalla', $request->mohalla);
            }
        } elseif ($mohallaMutvalli) {
            // Sub-Admin Access: strictly scoped to assigned mohalla
            $query->where('mohalla', $mohallaMutvalli->assigned_mohalla);
        } elseif ($isPublic) {
            // Public Broadcast Mode active: anyone in this masjid/madarsa can view collection list
            if ($request->filled('mohalla')) {
                $query->where('mohalla', $request->mohalla);
            }
        } else {
            // Default General Member: strictly personal ledger only
            $query->where('donor_user_id', $user->id);
        }

        // Status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search filter (donor name or phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('donor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Summary Stats
        $summaryQuery = clone $query;
        $summary = [
            'total_calculated' => (float)$summaryQuery->sum('calculated_amount'),
            'total_collected'  => (float)$summaryQuery->sum('paid_amount'),
            'total_balance'    => (float)$summaryQuery->sum('balance'),
            'total_donors'     => (int)$summaryQuery->count(),
            'paid_count'       => (int)(clone $query)->where('payment_status', 'paid')->count(),
            'partial_count'    => (int)(clone $query)->where('payment_status', 'partial')->count(),
            'unpaid_count'     => (int)(clone $query)->where('payment_status', 'unpaid')->count(),
        ];

        // Paginate results
        $ledgers = $query->orderBy('mohalla')->orderBy('id')->paginate($request->query('per_page', 50));

        $userRole = 'donor';
        if ($isMutvalli) {
            $userRole = 'mutvalli';
        } elseif ($mohallaMutvalli) {
            $userRole = 'mohalla_mutvalli';
        }

        return response()->json([
            'success' => true,
            'user_access_role' => $userRole,
            'is_public_broadcast' => $isPublic,
            'public_until' => $publicUntil,
            'days_remaining' => $daysRemaining,
            'summary' => $summary,
            'data' => $ledgers,
        ]);
    }

    /**
     * Record Offline/Cash Payment (Hisab-Kitab)
     */
    public function recordPayment(Request $request)
    {
        $request->validate([
            'ledger_id' => 'required_without_all:campaign_id,donor_user_id|nullable|exists:donation_ledgers,id',
            'campaign_id' => 'required_without:ledger_id|nullable|exists:donation_campaigns,id',
            'donor_user_id' => 'required_without:ledger_id|nullable|exists:users,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $ledger = null;
        if ($request->filled('ledger_id')) {
            $ledger = DonationLedger::findOrFail($request->ledger_id);
        } else {
            $campaign = DonationCampaign::findOrFail($request->campaign_id);
            $donor = \App\Models\User::findOrFail($request->donor_user_id);
            $ledger = DonationLedger::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'donor_user_id' => $donor->id,
                ],
                [
                    'masjid_id' => $campaign->masjid_id,
                    'madarsa_id' => $campaign->madarsa_id,
                    'mohalla' => $donor->mohalla ?? 'General',
                    'unit_count' => 1.00,
                    'calculated_amount' => (float)($campaign->rate_per_unit ?? $request->amount_paid),
                    'paid_amount' => 0.00,
                    'balance' => (float)($campaign->rate_per_unit ?? $request->amount_paid),
                    'payment_status' => 'unpaid',
                ]
            );
        }
        $user = $request->user()->load(['roles', 'memberProfile.category']);

        $isMutvalli = $this->checkIsMutvalli($user, $ledger->masjid_id, $ledger->madarsa_id);

        $isMohallaMutvalli = false;
        if ($ledger->masjid_id) {
            $isMohallaMutvalli = MohallaMutawalli::where('masjid_id', $ledger->masjid_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        } elseif ($ledger->madarsa_id) {
            $isMohallaMutvalli = MohallaMutawalli::where('madarsa_id', $ledger->madarsa_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        }

        if (!$isMutvalli && !$isMohallaMutvalli) {
            return response()->json(['message' => 'Unauthorized to record payments for this entry.'], 403);
        }

        $ledger->paid_amount += (float)$request->amount_paid;
        $ledger->recorded_by_user_id = $user->id;
        if ($request->filled('notes')) {
            $ledger->notes = $request->notes;
        }
        $ledger->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'data' => $ledger->fresh(['donor', 'recorder']),
        ]);
    }

    /**
     * Update Unit Count (e.g. Number of Family Heads / Land Area)
     */
    public function updateUnitCount(Request $request)
    {
        $request->validate([
            'ledger_id' => 'required|exists:donation_ledgers,id',
            'unit_count' => 'required|numeric|min:0.01',
        ]);

        $ledger = DonationLedger::with('campaign')->findOrFail($request->ledger_id);
        $user = $request->user()->load(['roles', 'memberProfile.category']);

        $isMutvalli = $this->checkIsMutvalli($user, $ledger->masjid_id, $ledger->madarsa_id);

        $isMohallaMutvalli = false;
        if ($ledger->masjid_id) {
            $isMohallaMutvalli = MohallaMutawalli::where('masjid_id', $ledger->masjid_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        } elseif ($ledger->madarsa_id) {
            $isMohallaMutvalli = MohallaMutawalli::where('madarsa_id', $ledger->madarsa_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        }

        if (!$isMutvalli && !$isMohallaMutvalli) {
            return response()->json(['message' => 'Unauthorized to update this ledger entry.'], 403);
        }

        $unitRate = (float)($ledger->campaign->rate_per_unit ?? 0);
        $units = (float)$request->unit_count;

        $ledger->unit_count = $units;
        $ledger->calculated_amount = $unitRate * $units;
        $ledger->save();

        return response()->json([
            'success' => true,
            'message' => 'Unit count and calculated amount updated successfully.',
            'data' => $ledger->fresh(['donor', 'campaign']),
        ]);
    }

    /**
     * Assign an offline cash donation amount to an applied donor.
     * Creates/updates the campaign donation ledger and notifies the donor via FCM.
     */
    public function assignDonationAmount(Request $request, FirebaseNotificationService $firebase)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'user_id'     => 'required|exists:users,id',
            'amount'      => 'required|numeric|min:1',
            'unit_count'  => 'nullable|numeric|min:0.01',
            'mohalla'     => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:1000',
            'masjid_id'   => 'nullable|exists:masjids,id',
            'madarsa_id'  => 'nullable|exists:madarsas,id',
        ]);

        $user = $request->user()->load(['roles', 'memberProfile.category']);
        $campaign = DonationCampaign::with(['masjid', 'madarsa'])->findOrFail($validated['campaign_id']);
        $masjidId = $campaign->masjid_id ?? $validated['masjid_id'] ?? null;
        $madarsaId = $campaign->madarsa_id ?? $validated['madarsa_id'] ?? null;

        // Authorization: Verify the requesting user is the Mutawalli / Admin or Sub-Admin of this place
        $isMutvalli = $this->checkIsMutvalli($user, $masjidId, $madarsaId);
        $isMohallaMutvalli = false;
        if ($masjidId) {
            $isMohallaMutvalli = MohallaMutawalli::where('masjid_id', $masjidId)
                ->where('user_id', $user->id)
                ->exists();
        } elseif ($madarsaId) {
            $isMohallaMutvalli = MohallaMutawalli::where('madarsa_id', $madarsaId)
                ->where('user_id', $user->id)
                ->exists();
        }

        $isAuthorized = $user->isSuperAdmin() || $isMutvalli || $isMohallaMutvalli;

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the Mutawalli or assigned sub-admins can assign donations.',
            ], 403);
        }

        // Determine unit count & total amount
        $unitCount = (float)($validated['unit_count'] ?? 1);
        $amount = (float)$validated['amount'];

        $targetUser = User::findOrFail($validated['user_id']);
        $mohalla = !empty($validated['mohalla']) 
            ? $validated['mohalla'] 
            : ($targetUser->mohalla ?: 'General');

        // Create or update the ledger record
        $ledger = DonationLedger::updateOrCreate(
            [
                'campaign_id'   => $campaign->id,
                'donor_user_id' => $targetUser->id,
            ],
            [
                'masjid_id'          => $masjidId,
                'madarsa_id'         => $madarsaId,
                'calculated_amount'  => $amount,
                'unit_count'         => $unitCount,
                'mohalla'            => $mohalla,
                'notes'              => $validated['notes'] ?? null,
                'recorded_by_user_id'=> $user->id,
                'payment_status'     => 'unpaid',
            ]
        );

        // Dispatch FCM push notification to the donor
        try {
            $placeName = $campaign->masjid?->name ?? $campaign->madarsa?->name ?? 'Masjid';
            $campaignTitle = $campaign->name ?? 'Donation Campaign';
            $title = "Donation Assigned: {$campaignTitle}";
            $body = "Assalamu Alaikum. An offline cash donation of ₹" . number_format($amount, 0) . 
                    " has been assigned to you for \"{$campaignTitle}\" at {$placeName}. Please pay this offline to the Mutawalli in cash.";

            $firebase->sendToUser(
                $targetUser,
                $title,
                $body,
                [
                    'type'        => 'donation_assigned',
                    'campaign_id' => (string) $campaign->id,
                    'ledger_id'   => (string) $ledger->id,
                    'amount'      => (string) $amount,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('FCM donation assignment notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Donation amount successfully assigned and donor notified.',
            'data'    => $ledger->fresh(['donor', 'campaign', 'masjid', 'madarsa']),
        ], 200);
    }
}
