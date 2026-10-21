<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MasjidExpense;
use App\Models\MohallaMutawalli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasjidExpenseController extends Controller
{
    /**
     * Check if user is authorized to manage expenses for a place
     */
    private function checkAuthorization($user, $masjidId = null, $madarsaId = null): bool
    {
        if ($user->isSuperAdmin() || $user->roles()->whereIn('slug', ['super_admin', 'admin'])->exists()) {
            return true;
        }

        if ($masjidId) {
            $masjid = Masjid::find($masjidId);
            if ($masjid && $masjid->user_id === $user->id) {
                return true;
            }

            if (MohallaMutawalli::where('masjid_id', $masjidId)->where('user_id', $user->id)->exists()) {
                return true;
            }

            if ($user->memberProfile && ($user->memberProfile->masjid_id == $masjidId || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjidId))) {
                return true;
            }
        }

        if ($madarsaId) {
            $madarsa = Madarsa::find($madarsaId);
            if ($madarsa && $madarsa->user_id === $user->id) {
                return true;
            }

            if (MohallaMutawalli::where('madarsa_id', $madarsaId)->where('user_id', $user->id)->exists()) {
                return true;
            }

            if ($user->memberProfile && ($user->memberProfile->madarsa_id == $madarsaId || ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id == $madarsaId))) {
                return true;
            }
        }

        return $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli'])->exists();
    }

    /**
     * List expenses & incomes with filters and pagination
     */
    public function index(Request $request)
    {
        $request->validate([
            'masjid_id'     => 'nullable|exists:masjids,id',
            'madarsa_id'    => 'nullable|exists:madarsas,id',
            'campaign_id'   => 'nullable|exists:donation_campaigns,id',
            'entry_type'    => 'nullable|in:expense,income',
            'category'      => 'nullable|string',
            'payment_mode'  => 'nullable|in:cash,bank_transfer,upi,cheque,other',
            'from_date'     => 'nullable|date',
            'to_date'       => 'nullable|date',
            'status'        => 'nullable|in:approved,pending,rejected',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ]);

        $query = MasjidExpense::with(['campaign:id,name', 'recorder:id,name']);

        if ($request->filled('masjid_id')) {
            $query->where('masjid_id', $request->masjid_id);
        }

        if ($request->filled('madarsa_id')) {
            $query->where('madarsa_id', $request->madarsa_id);
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        } elseif ($request->boolean('general_only')) {
            $query->whereNull('campaign_id');
        }

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $perPage = (int) $request->query('per_page', 20);
        $records = $query->orderBy('transaction_date', 'desc')
                         ->orderBy('id', 'desc')
                         ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $records,
        ]);
    }

    /**
     * Store a newly created expense or income entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'masjid_id'        => 'nullable|exists:masjids,id',
            'madarsa_id'       => 'nullable|exists:madarsas,id',
            'campaign_id'      => 'nullable|exists:donation_campaigns,id',
            'entry_type'       => 'required|in:expense,income',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'category'         => 'nullable|string|max:100',
            'payment_mode'     => 'nullable|in:cash,bank_transfer,upi,cheque,other',
            'transaction_date' => 'required|date|before_or_equal:today',
            'vendor_name'      => 'nullable|string|max:255',
            'receipt_file'     => 'nullable|image|max:5120',
            'notes'            => 'nullable|string|max:2000',
            'status'           => 'nullable|in:approved,pending,rejected',
        ]);

        $masjidId = $request->masjid_id;
        $madarsaId = $request->madarsa_id;

        // Auto-resolve place from campaign if passed
        if ($request->filled('campaign_id') && !$masjidId && !$madarsaId) {
            $campaign = DonationCampaign::find($request->campaign_id);
            if ($campaign) {
                $masjidId = $campaign->masjid_id;
                $madarsaId = $campaign->madarsa_id;
            }
        }

        if (!$masjidId && !$madarsaId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide masjid_id or madarsa_id.',
            ], 422);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, $masjidId, $madarsaId)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the Mutawalli or authorized administrators can record expenses.',
            ], 403);
        }

        // Handle file upload
        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('masjid_expenses/receipts', 'public');
        }

        $expense = MasjidExpense::create([
            'masjid_id'           => $masjidId,
            'madarsa_id'          => $madarsaId,
            'campaign_id'         => $request->campaign_id,
            'recorded_by_user_id' => $user->id,
            'entry_type'          => $request->entry_type,
            'title'               => $request->title,
            'amount'              => $request->amount,
            'category'            => $request->category ?: 'other',
            'payment_mode'        => $request->payment_mode ?: 'cash',
            'transaction_date'    => $request->transaction_date,
            'vendor_name'         => $request->vendor_name,
            'receipt_url'         => $receiptPath,
            'notes'               => $request->notes,
            'status'              => $request->status ?: 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->entry_type) . ' recorded successfully.',
            'data'    => $expense->load(['campaign:id,name', 'recorder:id,name']),
        ], 201);
    }

    /**
     * Display a single expense / income entry
     */
    public function show($id)
    {
        $expense = MasjidExpense::with([
            'masjid:id,name',
            'madarsa:id,name',
            'campaign:id,name',
            'recorder:id,name,phone',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $expense,
        ]);
    }

    /**
     * Update an existing expense or income entry
     */
    public function update(Request $request, $id)
    {
        $expense = MasjidExpense::findOrFail($id);
        $user = $request->user();

        if (!$this->checkAuthorization($user, $expense->masjid_id, $expense->madarsa_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You cannot modify this record.',
            ], 403);
        }

        $request->validate([
            'campaign_id'      => 'nullable|exists:donation_campaigns,id',
            'entry_type'       => 'nullable|in:expense,income',
            'title'            => 'nullable|string|max:255',
            'amount'           => 'nullable|numeric|min:0.01',
            'category'         => 'nullable|string|max:100',
            'payment_mode'     => 'nullable|in:cash,bank_transfer,upi,cheque,other',
            'transaction_date' => 'nullable|date|before_or_equal:today',
            'vendor_name'      => 'nullable|string|max:255',
            'receipt_file'     => 'nullable|image|max:5120',
            'notes'            => 'nullable|string|max:2000',
            'status'           => 'nullable|in:approved,pending,rejected',
        ]);

        // Handle file replacement
        if ($request->hasFile('receipt_file')) {
            if ($expense->receipt_url && Storage::disk('public')->exists($expense->receipt_url)) {
                Storage::disk('public')->delete($expense->receipt_url);
            }
            $expense->receipt_url = $request->file('receipt_file')->store('masjid_expenses/receipts', 'public');
        }

        $expense->fill($request->only([
            'campaign_id',
            'entry_type',
            'title',
            'amount',
            'category',
            'payment_mode',
            'transaction_date',
            'vendor_name',
            'notes',
            'status',
        ]));

        $expense->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully.',
            'data'    => $expense->fresh(['campaign:id,name', 'recorder:id,name']),
        ]);
    }

    /**
     * Remove / soft-delete the specified transaction
     */
    public function destroy(Request $request, $id)
    {
        $expense = MasjidExpense::findOrFail($id);
        $user = $request->user();

        if (!$this->checkAuthorization($user, $expense->masjid_id, $expense->madarsa_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You cannot delete this record.',
            ], 403);
        }

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully.',
        ]);
    }

    /**
     * Consolidated Financial & Accounting Summary
     */
    public function summary(Request $request)
    {
        $request->validate([
            'masjid_id'   => 'nullable|exists:masjids,id',
            'madarsa_id'  => 'nullable|exists:madarsas,id',
            'campaign_id' => 'nullable|exists:donation_campaigns,id',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date',
        ]);

        $masjidId = $request->masjid_id;
        $madarsaId = $request->madarsa_id;
        $campaignId = $request->campaign_id;

        if ($campaignId && !$masjidId && !$madarsaId) {
            $c = DonationCampaign::find($campaignId);
            if ($c) {
                $masjidId = $c->masjid_id;
                $madarsaId = $c->madarsa_id;
            }
        }

        if (!$masjidId && !$madarsaId && !$campaignId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide masjid_id, madarsa_id, or campaign_id.',
            ], 422);
        }

        // 1. Total Campaign Collections (from donation_ledgers paid_amount)
        $ledgerQuery = DonationLedger::query();
        if ($campaignId) {
            $ledgerQuery->where('campaign_id', $campaignId);
        } elseif ($masjidId) {
            $ledgerQuery->where('masjid_id', $masjidId);
        } elseif ($madarsaId) {
            $ledgerQuery->where('madarsa_id', $madarsaId);
        }

        $totalCampaignCollected = (float) $ledgerQuery->sum('paid_amount');

        // 2. Base query for Masjid Expenses
        $expenseBase = MasjidExpense::query()
            ->where('status', 'approved')
            ->forPlace($masjidId, $madarsaId);

        if ($campaignId) {
            $expenseBase->where('campaign_id', $campaignId);
        }

        if ($request->filled('from_date')) {
            $expenseBase->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $expenseBase->where('transaction_date', '<=', $request->to_date);
        }

        // Inflows & Outflows
        $totalGeneralIncome = (float) (clone $expenseBase)->where('entry_type', 'income')->sum('amount');
        $totalIncome = $totalCampaignCollected + $totalGeneralIncome;

        $totalCampaignExpenses = (float) (clone $expenseBase)->where('entry_type', 'expense')->whereNotNull('campaign_id')->sum('amount');
        $totalGeneralExpenses = (float) (clone $expenseBase)->where('entry_type', 'expense')->whereNull('campaign_id')->sum('amount');
        $totalExpenses = $totalCampaignExpenses + $totalGeneralExpenses;

        $netOperatingBalance = $totalIncome - $totalExpenses;

        // 3. Category Breakdown (Expenses only)
        $categoryBreakdown = (clone $expenseBase)
            ->where('entry_type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category,
                'total'    => (float) $item->total,
                'count'    => (int) $item->count,
            ]);

        // 4. Campaign-wise Breakdown
        $campaignsQuery = DonationCampaign::query()
            ->when($masjidId, fn($q) => $q->where('masjid_id', $masjidId))
            ->when($madarsaId, fn($q) => $q->where('madarsa_id', $madarsaId))
            ->when($campaignId, fn($q) => $q->where('id', $campaignId));

        $campaignBreakdowns = $campaignsQuery->get()->map(function ($camp) use ($request) {
            $collected = (float) DonationLedger::where('campaign_id', $camp->id)->sum('paid_amount');
            $spent = (float) MasjidExpense::where('campaign_id', $camp->id)
                ->where('entry_type', 'expense')
                ->where('status', 'approved')
                ->when($request->filled('from_date'), fn($q) => $q->where('transaction_date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn($q) => $q->where('transaction_date', '<=', $request->to_date))
                ->sum('amount');

            return [
                'campaign_id'   => $camp->id,
                'campaign_name' => $camp->name,
                'collected'     => $collected,
                'spent'         => $spent,
                'balance'       => $collected - $spent,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'total_campaign_collected' => $totalCampaignCollected,
                'total_general_income'    => $totalGeneralIncome,
                'total_income'            => $totalIncome,
                'total_campaign_expenses' => $totalCampaignExpenses,
                'total_general_expenses'  => $totalGeneralExpenses,
                'total_expenses'          => $totalExpenses,
                'net_operating_balance'   => $netOperatingBalance,
                'category_breakdown'      => $categoryBreakdown,
                'campaign_breakdowns'     => $campaignBreakdowns,
            ],
        ]);
    }
}
