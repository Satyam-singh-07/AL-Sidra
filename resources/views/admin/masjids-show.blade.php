@extends('admin.layouts.app')

@section('title', 'Masjid Details')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-success mb-1">{{ $masjid->name }}</h2>
        <p class="text-muted mb-0">
            <i class="fas fa-map-marker-alt me-1"></i>
            {{ $masjid->address }}
        </p>
    </div>

    <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <!-- LEFT -->
    <div class="col-md-8">

        <!-- BASIC INFO -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Basic Information</h5>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Community:</strong>
                        {{ $masjid->community->name ?? 'N/A' }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong>
                        <span class="badge
                            @if($masjid->status=='active') bg-success
                            @elseif($masjid->status=='pending') bg-warning text-dark
                            @else bg-danger
                            @endif">
                            {{ ucfirst($masjid->status) }}
                        </span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Latitude:</strong> {{ $masjid->latitude }}
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Longitude:</strong> {{ $masjid->longitude }}
                    </div>

                    <div class="col-md-12 mt-2">
                        <strong>Created By:</strong>
                        {{ $masjid->user->name ?? 'Admin' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- MAP -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Location Map</h5>

                <iframe
                    width="100%"
                    height="300"
                    style="border:0"
                    loading="lazy"
                    allowfullscreen
                    src="https://www.google.com/maps?q={{ $masjid->latitude }},{{ $masjid->longitude }}&hl=en&z=16&output=embed">
                </iframe>
            </div>
        </div>

        <!-- GALLERY -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Masjid Images</h5>

                <div class="row">
                    @forelse ($masjid->images as $image)
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

    <!-- RIGHT -->
    <div class="col-md-4">

        <!-- DOCUMENTS -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3">Documents</h5>

                @if ($masjid->passbook)
                    <a href="{{ asset('storage/'.$masjid->passbook) }}"
                       target="_blank"
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-file-pdf me-1"></i> View Passbook
                    </a>
                @else
                    <p class="text-muted">No passbook uploaded</p>
                @endif
            </div>
        </div>

        <!-- VIDEO -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Masjid Video</h5>

                @if ($masjid->video)
                    <video controls width="100%">
                        <source src="{{ asset('storage/'.$masjid->video) }}">
                    </video>
                @else
                    <p class="text-muted">No video uploaded</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
