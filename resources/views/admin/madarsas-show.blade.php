@extends('admin.layouts.app')

@section('title', 'Madarsa Details')

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-success mb-1">{{ $madarsa->name }}</h2>
        <p class="text-muted mb-0">
            <i class="fas fa-map-marker-alt me-1"></i>
            {{ $madarsa->address }}
        </p>
    </div>

    <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    {{-- LEFT --}}
    <div class="col-md-8">

        {{-- Basic Info --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Basic Information</h5>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Gender:</strong>
                        <span class="badge bg-secondary">
                            {{ ucfirst($madarsa->gender) }}
                        </span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong>
                        <span class="badge
                            @if($madarsa->status=='active') bg-success
                            @elseif($madarsa->status=='pending') bg-warning text-dark
                            @else bg-danger @endif">
                            {{ ucfirst($madarsa->status) }}
                        </span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Community:</strong>
                        {{ $madarsa->community->name ?? 'N/A' }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Created By:</strong>
                        {{ $madarsa->user->name ?? 'Admin' }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Latitude:</strong> {{ $madarsa->latitude }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Longitude:</strong> {{ $madarsa->longitude }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Map --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Location Map</h5>

                <iframe
                    width="100%"
                    height="300"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                    src="https://www.google.com/maps?q={{ $madarsa->latitude }},{{ $madarsa->longitude }}&hl=en&z=16&output=embed">
                </iframe>
            </div>
        </div>

        {{-- Images --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Madarsa Images</h5>

                <div class="row">
                    @forelse($madarsa->images as $image)
                        <div class="col-md-3 mb-3">
                            <img src="{{ asset('storage/'.$image->image_path) }}"
                                 class="img-fluid rounded border">
                        </div>
                    @empty
                        <p class="text-muted">No images uploaded</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT --}}
    <div class="col-md-4">

        {{-- Documents --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Documents</h5>

                @if($madarsa->passbook)
                    <a href="{{ asset('storage/'.$madarsa->passbook) }}"
                       target="_blank"
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-file-pdf me-1"></i> View Passbook
                    </a>
                @else
                    <p class="text-muted">No passbook uploaded</p>
                @endif

                <p class="mt-3 mb-1">
                    <strong>Registration Number:</strong><br>
                    {{ $madarsa->registration_number ?? 'N/A' }}
                </p>

                <p>
                    <strong>Registration Date:</strong><br>
                    {{ $madarsa->registration_date
                        ? $madarsa->registration_date->format('d M Y')
                        : 'N/A' }}
                </p>
            </div>
        </div>

        {{-- Video --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Madarsa Video</h5>

                @if($madarsa->video)
                    <video controls width="100%">
                        <source src="{{ asset('storage/'.$madarsa->video) }}">
                    </video>
                @else
                    <p class="text-muted">No video uploaded</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
