@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Transaction #{{ $payment->id }}</h2>
            <p class="text-muted mb-0">Detailed information about the payment</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Razorpay Order ID</div>
                        <div class="col-sm-8 fw-bold">{{ $payment->razorpay_order_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Razorpay Payment ID</div>
                        <div class="col-sm-8 fw-bold">{{ $payment->razorpay_payment_id ?: 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Amount</div>
                        <div class="col-sm-8 fw-bold text-success">₹{{ number_format($payment->amount / 100, 2) }}
                            ({{ $payment->currency }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Donation Type</div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary">{{ ucfirst($payment->donation_type) ?: 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status</div>
                        <div class="col-sm-8">
                            <span
                                class="badge @if ($payment->status == 'success') bg-success @elseif($payment->status == 'pending') bg-warning text-dark @else bg-danger @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Date & Time</div>
                        <div class="col-sm-8">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    @if ($payment->razorpay_signature)
                        <div class="row mb-0">
                            <div class="col-sm-4 text-muted">Signature</div>
                            <div class="col-sm-8 small text-truncate">{{ $payment->razorpay_signature }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Purpose / Paymentable</h5>
                </div>
                <div class="card-body">
                    @if ($payment->paymentable)
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Type</div>
                            <div class="col-sm-8 fw-bold">{{ class_basename($payment->paymentable_type) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Name</div>
                            <div class="col-sm-8 fw-bold">{{ $payment->paymentable->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-sm-4 text-muted">ID</div>
                            <div class="col-sm-8 fw-bold">#{{ $payment->paymentable_id }}</div>
                        </div>
                    @else
                        <p class="text-muted mb-0">General Donation (No specific target)</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">User Details</h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($payment->user->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $payment->user->name ?? 'Anonymous' }}</h5>
                    <p class="text-muted mb-0">{{ $payment->user->email ?? '' }}</p>
                    <hr>
                    <div class="text-start">
                        <small class="text-muted d-block mb-1">User ID</small>
                        <p class="fw-bold mb-3">#{{ $payment->user_id ?? 'N/A' }}</p>

                        <small class="text-muted d-block mb-1">Registered On</small>
                        <p class="fw-bold mb-0">{{ $payment->user ? $payment->user->created_at->format('d M Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
