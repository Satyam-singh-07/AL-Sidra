@extends('admin.layouts.app')

@section('title', 'Yateems Help Details')

@section('content')

<style>
    .info-label {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .info-value {
        font-weight: 600;
    }

    .help-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .doc-image {
        width: 100%;
        max-height: 250px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
</style>

<div class="mb-3">
    <a href="{{ route('yateems-helps.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1">{{ $yateemsHelp->title }}</h4>
                <span class="text-muted small">
                    Created by: {{ $yateemsHelp->user->name ?? 'N/A' }}
                </span>
            </div>

            <span class="badge bg-info text-dark">
                {{ ucfirst($yateemsHelp->status ?? 'pending') }}
            </span>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="info-label">Description</label>
            <p class="info-value mb-0">
                {{ $yateemsHelp->description }}
            </p>
        </div>

        <!-- Images -->
        <div class="mb-4">
            <label class="info-label d-block mb-2">Images</label>
            <div class="d-flex flex-wrap gap-3">
                @forelse ($yateemsHelp->images as $image)
                    <img src="{{ asset('storage/' . $image->image) }}" class="help-image">
                @empty
                    <span class="text-muted">No images uploaded</span>
                @endforelse
            </div>
        </div>

        <!-- Video -->
        <div class="mb-4">
            <label class="info-label d-block mb-2">Video</label>

            @if ($yateemsHelp->video)
                <video controls width="100%" style="max-height: 400px;">
                    <source src="{{ asset('storage/' . $yateemsHelp->video) }}">
                    Your browser does not support the video tag.
                </video>
            @else
                <span class="text-muted">No video uploaded</span>
            @endif
        </div>

        <hr>

        <!-- Bank Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="info-label">Bank Name</label>
                <div class="info-value">{{ $yateemsHelp->bank_name }}</div>
            </div>

            <div class="col-md-4">
                <label class="info-label">IFSC Code</label>
                <div class="info-value">{{ $yateemsHelp->ifsc_code }}</div>
            </div>

            <div class="col-md-4">
                <label class="info-label">Account Number</label>
                <div class="info-value">
                    {{ Str::mask($yateemsHelp->account_no, '*', 0, strlen($yateemsHelp->account_no) - 4) }}
                </div>
            </div>
        </div>

        <!-- UPI + QR -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="info-label">UPI ID</label>
                <div class="info-value">{{ $yateemsHelp->upi_id ?? 'N/A' }}</div>
            </div>

            <div class="col-md-6">
                <label class="info-label d-block">QR Code</label>
                @if ($yateemsHelp->qr_code)
                    <img src="{{ asset('storage/' . $yateemsHelp->qr_code) }}"
                         class="doc-image mt-2">
                @else
                    <span class="text-muted">No QR code uploaded</span>
                @endif
            </div>
        </div>

        <hr>

        <!-- Aadhaar Documents (Admin only / if exists) -->
        <div>
            <h6 class="fw-bold mb-3">Aadhaar Documents</h6>

            @if ($yateemsHelp->document)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="info-label d-block mb-1">Aadhaar Front</label>
                        <img src="{{ asset('storage/' . $yateemsHelp->document->aadhaar_front) }}"
                             class="doc-image">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="info-label d-block mb-1">Aadhaar Back</label>
                        <img src="{{ asset('storage/' . $yateemsHelp->document->aadhaar_back) }}"
                             class="doc-image">
                    </div>
                </div>
            @else
                <span class="text-muted">Aadhaar documents not submitted</span>
            @endif
        </div>

    </div>
</div>

@endsection
