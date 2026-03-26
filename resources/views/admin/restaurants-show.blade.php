@extends('admin.layouts.app')

@section('title', 'Restaurant Details')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <div>
                <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit Restaurant
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- BASIC INFO --}}
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $restaurant->name }}</h4>
                        <p class="text-muted">{{ $restaurant->address }}</p>
                    </div>
                    <div>
                        <span class="badge {{ $restaurant->status == 'approved' ? 'bg-success' : ($restaurant->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }} p-2">
                            {{ ucfirst($restaurant->status) }}
                        </span>
                    </div>
                </div>

                @if($restaurant->description)
                    <p class="mt-2">{{ $restaurant->description }}</p>
                @endif

                <hr>

                {{-- CONTACT INFO --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold"><i class="fas fa-address-book me-2"></i>Contact Information</h6>
                        <div class="mt-2">
                            <div><strong>Phone:</strong> {{ $restaurant->contact_number ?? '—' }}</div>
                            <div><strong>Opening Time:</strong> {{ $restaurant->opening_time ? date('h:i A', strtotime($restaurant->opening_time)) : '—' }}</div>
                            <div><strong>Closing Time:</strong> {{ $restaurant->closing_time ? date('h:i A', strtotime($restaurant->closing_time)) : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 border-start">
                        <h6 class="fw-bold"><i class="fas fa-user me-2"></i>Submitted By</h6>
                        <div class="mt-2">
                            <div><strong>Name:</strong> {{ $restaurant->user->name ?? '—' }}</div>
                            <div><strong>Email:</strong> {{ $restaurant->user->email ?? '—' }}</div>
                            <div><strong>Phone:</strong> {{ $restaurant->user->phone ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- LOCATION --}}
                <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                @if ($restaurant->latitude && $restaurant->longitude)
                    <div class="mb-3">
                        <a href="https://www.google.com/maps?q={{ $restaurant->latitude }},{{ $restaurant->longitude }}"
                            target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> View on Google Maps
                        </a>
                    </div>
                    <div class="mb-4">
                        <iframe width="100%" height="300" style="border-radius:12px;border:1px solid #dee2e6" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q={{ $restaurant->latitude }},{{ $restaurant->longitude }}&output=embed">
                        </iframe>
                    </div>
                @else
                    <p class="text-muted">No location coordinates provided</p>
                @endif

                <hr>

                <div class="row">
                    {{-- MENU IMAGE --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="fas fa-book-open me-2"></i>Menu Image</h6>
                        @if ($restaurant->menu_image)
                            <a href="{{ $restaurant->menu_image_url }}" target="_blank">
                                <img src="{{ $restaurant->menu_image_url }}" style="max-width:100%; max-height:300px; border-radius:8px; border:1px solid #dee2e6" class="mb-4 shadow-sm">
                            </a>
                        @else
                            <p class="text-muted">No menu image provided</p>
                        @endif
                    </div>

                    {{-- VIDEO --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="fas fa-video me-2"></i>Video Tour</h6>
                        @if ($restaurant->video)
                            <video controls style="max-width:100%; border-radius:8px; border:1px solid #dee2e6" class="mb-4 shadow-sm">
                                <source src="{{ $restaurant->video_url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <p class="text-muted">No video provided</p>
                        @endif
                    </div>
                </div>

                <hr>

                {{-- IMAGES GALLERY --}}
                <h6 class="fw-bold mb-3"><i class="fas fa-images me-2"></i>Gallery Images</h6>
                @if($restaurant->images->count() > 0)
                    <div class="row g-3">
                        @foreach ($restaurant->images as $image)
                            <div class="col-6 col-md-3 col-lg-2">
                                <a href="{{ $image->image_url }}" target="_blank">
                                    <img src="{{ $image->image_url }}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:1px solid #dee2e6" class="shadow-sm hover-zoom">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No gallery images provided</p>
                @endif

                <hr>

                {{-- ACTIONS --}}
                @if($restaurant->status == 'pending')
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.restaurants.approve', $restaurant) }}">
                        @csrf
                        <button class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                    </form>

                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                </div>
                @endif

            </div>
        </div>

    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.restaurants.reject', $restaurant) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Restaurant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Remark (Optional)</label>
                            <textarea name="admin_remark" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
