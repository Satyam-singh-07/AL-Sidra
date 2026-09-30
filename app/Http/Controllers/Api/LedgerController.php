<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MasjidBroadcastSetting;
use App\Models\MohallaMutawalli;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    /**
     * Get Ledger entries with Privacy Controls & Summary Stats
     */
    public function index(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'mohalla' => 'nullable|string',
            'payment_status' => 'nullable|in:paid,partial,unpaid',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $campaign = DonationCampaign::findOrFail($request->campaign_id);

        $masjidId = $campaign->masjid_id;
        $madarsaId = $campaign->madarsa_id;

        // Check ownership & role
        $isMutawalli = false;
        if ($masjidId) {
            $isMutawalli = Masjid::where('id', $masjidId)->where('user_id', $user->id)->exists();
        } elseif ($madarsaId) {
            $isMutawalli = Madarsa::where('id', $madarsaId)->where('user_id', $user->id)->exists();
        }

        $isSuperAdmin = $user->isSuperAdmin();

        $mohallaMutawalli = null;
        if ($masjidId) {
            $mohallaMutawalli = MohallaMutawalli::where('masjid_id', $masjidId)->where('user_id', $user->id)->first();
        } elseif ($madarsaId) {
            $mohallaMutawalli = MohallaMutawalli::where('madarsa_id', $madarsaId)->where('user_id', $user->id)->first();
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
        if ($isMutawalli || $isSuperAdmin) {
            // Full Admin Access: can view all and filter by mohalla
            if ($request->filled('mohalla')) {
                $query->where('mohalla', $request->mohalla);
            }
        } elseif ($mohallaMutawalli) {
            // Sub-Admin Access: strictly scoped to assigned mohalla
            $query->where('mohalla', $mohallaMutawalli->assigned_mohalla);
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

        // Calculate Summary Stats from the scoped query (before pagination)
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
        if ($isMutawalli || $isSuperAdmin) {
            $userRole = 'mutawalli';
        } elseif ($mohallaMutawalli) {
            $userRole = 'mohalla_mutawalli';
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
            'ledger_id' => 'required|exists:donation_ledgers,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $ledger = DonationLedger::findOrFail($request->ledger_id);
        $user = $request->user();

        // Check permission
        $isMutawalli = false;
        if ($ledger->masjid_id) {
            $isMutawalli = Masjid::where('id', $ledger->masjid_id)->where('user_id', $user->id)->exists();
        } elseif ($ledger->madarsa_id) {
            $isMutawalli = Madarsa::where('id', $ledger->madarsa_id)->where('user_id', $user->id)->exists();
        }

        $isMohallaMutawalli = false;
        if ($ledger->masjid_id) {
            $isMohallaMutawalli = MohallaMutawalli::where('masjid_id', $ledger->masjid_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        } elseif ($ledger->madarsa_id) {
            $isMohallaMutawalli = MohallaMutawalli::where('madarsa_id', $ledger->madarsa_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        }

        if (!$isMutawalli && !$isMohallaMutawalli && !$user->isSuperAdmin()) {
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
        $user = $request->user();

        // Check permission
        $isMutawalli = false;
        if ($ledger->masjid_id) {
            $isMutawalli = Masjid::where('id', $ledger->masjid_id)->where('user_id', $user->id)->exists();
        } elseif ($ledger->madarsa_id) {
            $isMutawalli = Madarsa::where('id', $ledger->madarsa_id)->where('user_id', $user->id)->exists();
        }

        $isMohallaMutawalli = false;
        if ($ledger->masjid_id) {
            $isMohallaMutawalli = MohallaMutawalli::where('masjid_id', $ledger->masjid_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        } elseif ($ledger->madarsa_id) {
            $isMohallaMutawalli = MohallaMutawalli::where('madarsa_id', $ledger->madarsa_id)
                ->where('user_id', $user->id)
                ->where('assigned_mohalla', $ledger->mohalla)
                ->exists();
        }

        if (!$isMutawalli && !$isMohallaMutawalli && !$user->isSuperAdmin()) {
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
}
