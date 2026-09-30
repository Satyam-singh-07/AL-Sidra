<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chanda Collection Sheet - {{ $campaign->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1A5040;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            color: #1A5040;
            font-size: 18px;
        }
        .header p {
            margin: 2px 0;
            color: #666;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        table.data-table th {
            background-color: #1A5040;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-partial {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer td {
            padding-top: 40px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $place ? $place->name : 'AL-SIDRA' }}</h1>
        <p>{{ $place ? $place->address : '' }}</p>
        <p><strong>Campaign:</strong> {{ $campaign->name }} | <strong>Category:</strong> {{ ucfirst($campaign->category) }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Rate Per Unit:</strong> {{ $campaign->rate_per_unit ? '₹ ' . number_format($campaign->rate_per_unit, 2) : 'N/A' }}</td>
            <td class="text-right"><strong>Print Date:</strong> {{ now()->format('d M Y, h:i A') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Donor Name</th>
                <th style="width: 15%;">Phone</th>
                <th style="width: 15%;">Mohalla</th>
                <th class="text-right" style="width: 10%;">Calculated</th>
                <th class="text-right" style="width: 10%;">Paid</th>
                <th class="text-right" style="width: 10%;">Balance</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totCalc = 0;
                $totPaid = 0;
                $totBal = 0;
            @endphp
            @forelse($ledgers as $index => $ledger)
                @php
                    $totCalc += (float)$ledger->calculated_amount;
                    $totPaid += (float)$ledger->paid_amount;
                    $totBal += (float)$ledger->balance;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $ledger->donor->name ?? 'N/A' }}</td>
                    <td>{{ $ledger->donor->phone ?? 'N/A' }}</td>
                    <td>{{ $ledger->mohalla ?? 'General' }}</td>
                    <td class="text-right">₹ {{ number_format($ledger->calculated_amount, 2) }}</td>
                    <td class="text-right">₹ {{ number_format($ledger->paid_amount, 2) }}</td>
                    <td class="text-right">₹ {{ number_format($ledger->balance, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $ledger->payment_status }}">
                            {{ strtoupper($ledger->payment_status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No ledger entries found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f1f1; font-weight: bold;">
                <td colspan="4" class="text-right">TOTALS:</td>
                <td class="text-right">₹ {{ number_format($totCalc, 2) }}</td>
                <td class="text-right">₹ {{ number_format($totPaid, 2) }}</td>
                <td class="text-right">₹ {{ number_format($totBal, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td style="width: 50%;">
                ___________________________<br>
                <strong>Mutawalli / Imam Signature</strong>
            </td>
            <td style="width: 50%; text-align: right;">
                ___________________________<br>
                <strong>Mohalla Mutawalli Signature</strong>
            </td>
        </tr>
    </table>
</body>
</html>
