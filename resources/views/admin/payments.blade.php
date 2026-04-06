@extends('admin.layouts.app')

@section('title', 'Payment Transactions')

@section('content')

    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .payment-info {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .stat-card {
            border: none;
            border-left: 4px solid #198754;
        }
    </style>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Payment Transactions</h2>
            <p class="text-muted mb-0">Track and manage all donation payments</p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Success Amount</h6>
                    <h4 class="fw-bold mb-0">₹{{ number_format($stats['total_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100" style="border-left-color: #0dcaf0;">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Transactions</h6>
                    <h4 class="fw-bold mb-0">{{ $stats['total_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100" style="border-left-color: #20c997;">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Successful</h6>
                    <h4 class="fw-bold mb-0">{{ $stats['success_count'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Pending</h6>
                    <h4 class="fw-bold mb-0">{{ $stats['pending_count'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by Order ID, Payment ID, Name or Email">
        </div>

        <div class="col-md-3">
            <select name="donation_type" class="form-select">
                <option value="">All Donation Types</option>
                @php
                    $donationTypes = \App\Models\Payment::whereNotNull('donation_type')->distinct()->pluck('donation_type');
                @endphp
                @foreach ($donationTypes as $type)
                    <option value="{{ $type }}" {{ request('donation_type') == $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-success w-100">Filter</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>User Details</th>
                        <th>Amount</th>
                        <th>Donation Type</th>
                        <th>Purpose</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="fw-bold">#{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}
                            </td>

                            <td>
                                <div>
                                    <h6 class="mb-0">{{ $payment->user->name ?? 'Anonymous' }}</h6>
                                    <small class="text-muted">{{ $payment->user->email ?? '' }}</small>
                                </div>
                            </td>

                            <td>
                                <span class="fw-bold">₹{{ number_format($payment->amount / 100, 2) }}</span>
                            </td>

                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ ucfirst($payment->donation_type) ?: 'N/A' }}
                                </span>
                            </td>

                            <td>
                                @if ($payment->paymentable)
                                    <small class="text-muted">
                                        {{ class_basename($payment->paymentable_type) }}:
                                        {{ $payment->paymentable->name ?? 'N/A' }}
                                    </small>
                                @else
                                    <small class="text-muted">General</small>
                                @endif
                            </td>

                            <td class="payment-info">
                                {{ $payment->razorpay_order_id }}
                                @if ($payment->razorpay_payment_id)
                                    <br><small>Pay ID: {{ $payment->razorpay_payment_id }}</small>
                                @endif
                            </td>

                            <td>
                                <span
                                    class="badge status-badge
        @if ($payment->status == 'success') bg-success
        @elseif($payment->status == 'pending') bg-warning text-dark
        @else bg-danger @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No payment transactions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $payments])
            </div>
        </div>
    </div>

@endsection
