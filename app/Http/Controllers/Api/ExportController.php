<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MohallaMutawalli;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Download CSV / Excel Collection Sheet
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'mohalla' => 'nullable|string',
            'payment_status' => 'nullable|in:paid,partial,unpaid',
        ]);

        $campaign = DonationCampaign::with(['masjid', 'madarsa'])->findOrFail($request->campaign_id);
        $user = $request->user();

        // RBAC Check
        $this->authorizeExport($campaign, $user, $request->mohalla);

        $query = DonationLedger::with('donor')
            ->where('campaign_id', $campaign->id);

        if ($request->filled('mohalla')) {
            $query->where('mohalla', $request->mohalla);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $ledgers = $query->orderBy('mohalla')->orderBy('id')->get();

        $placeName = $campaign->masjid ? $campaign->masjid->name : ($campaign->madarsa ? $campaign->madarsa->name : 'Place');
        $fileName = 'chanda_ledger_' . str_replace(' ', '_', strtolower($campaign->name)) . '_' . date('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($ledgers, $campaign, $placeName) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel Hindi/Urdu compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Metadata Header Rows
            fputcsv($handle, [$placeName, 'Campaign: ' . $campaign->name, 'Category: ' . ucfirst($campaign->category)]);
            fputcsv($handle, ['Exported At: ' . now()->toDayDateTimeString()]);
            fputcsv($handle, []); // Empty line

            // Table Columns
            fputcsv($handle, [
                'S.No',
                'Donor Name',
                'Phone',
                'Mohalla',
                'Unit Count',
                'Calculated Amount (₹)',
                'Paid Amount (₹)',
                'Balance (₹)',
                'Payment Status',
                'Notes',
            ]);

            $index = 1;
            $totalCalculated = 0;
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($ledgers as $ledger) {
                $calc = (float)$ledger->calculated_amount;
                $paid = (float)$ledger->paid_amount;
                $bal  = (float)$ledger->balance;

                $totalCalculated += $calc;
                $totalPaid += $paid;
                $totalBalance += $bal;

                fputcsv($handle, [
                    $index++,
                    $ledger->donor->name ?? 'N/A',
                    $ledger->donor->phone ?? 'N/A',
                    $ledger->mohalla ?? 'General',
                    $ledger->unit_count ?? 1,
                    number_format($calc, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($bal, 2, '.', ''),
                    strtoupper($ledger->payment_status),
                    $ledger->notes ?? '',
                ]);
            }

            // Summary Footer Row
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                '',
                '',
                number_format($totalCalculated, 2, '.', ''),
                number_format($totalPaid, 2, '.', ''),
                number_format($totalBalance, 2, '.', ''),
                '',
                '',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download PDF Collection Sheet
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'mohalla' => 'nullable|string',
            'payment_status' => 'nullable|in:paid,partial,unpaid',
        ]);

        $campaign = DonationCampaign::with(['masjid', 'madarsa'])->findOrFail($request->campaign_id);
        $user = $request->user();

        // RBAC Check
        $this->authorizeExport($campaign, $user, $request->mohalla);

        $query = DonationLedger::with('donor')
            ->where('campaign_id', $campaign->id);

        if ($request->filled('mohalla')) {
            $query->where('mohalla', $request->mohalla);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $ledgers = $query->orderBy('mohalla')->orderBy('id')->get();
        $place = $campaign->masjid ?? $campaign->madarsa;

        // If Barryvdh DomPDF is installed
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.ledger_pdf', [
                'campaign' => $campaign,
                'place' => $place,
                'ledgers' => $ledgers,
            ]);

            return $pdf->download("ledger_{$campaign->id}.pdf");
        }

        // Fallback to CSV if PDF package is not yet installed
        return $this->exportCsv($request);
    }

    /**
     * Check if caller has permission to export
     */
    private function authorizeExport(DonationCampaign $campaign, $user, ?string $mohalla): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $isOwner = false;
        if ($campaign->masjid_id) {
            $isOwner = Masjid::where('id', $campaign->masjid_id)->where('user_id', $user->id)->exists();
        } elseif ($campaign->madarsa_id) {
            $isOwner = Madarsa::where('id', $campaign->madarsa_id)->where('user_id', $user->id)->exists();
        }

        if ($isOwner) {
            return;
        }

        // Check if caller is Mohalla Mutawalli
        $mohallaMutawalli = null;
        if ($campaign->masjid_id) {
            $mohallaMutawalli = MohallaMutawalli::where('masjid_id', $campaign->masjid_id)->where('user_id', $user->id)->first();
        } elseif ($campaign->madarsa_id) {
            $mohallaMutawalli = MohallaMutawalli::where('madarsa_id', $campaign->madarsa_id)->where('user_id', $user->id)->first();
        }

        if ($mohallaMutawalli && (!$mohalla || $mohalla === $mohallaMutawalli->assigned_mohalla)) {
            return;
        }

        abort(403, 'Unauthorized to export collection sheet for this campaign.');
    }
}
