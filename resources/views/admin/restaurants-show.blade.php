@extends('admin.layouts.app')

@section('title', 'Restaurant Details')

@section('content')

    <div class="container-fluid">

        <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary mb-3">
            ← Back
        </a>

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- BASIC INFO --}}
                <h4 class="fw-bold">{{ $restaurant->name }}</h4>
                <p class="text-muted mb-2">{{ $restaurant->address }}</p>

                <p>{{ $restaurant->description }}</p>

                <hr>

                {{-- SUBMITTED BY --}}
                <h6 class="fw-bold">Submitted By</h6>
                <div class="mb-3">
                    <div><strong>Name:</strong> {{ $restaurant->user->name ?? '—' }}</div>
                    <div><strong>Email:</strong> {{ $restaurant->user->email ?? '—' }}</div>
                    <div><strong>Phone:</strong> {{ $restaurant->user->phone ?? '—' }}</div>
                    <div class="mb-3">
                        <strong>Restaurant Contact Number:</strong> {{ $restaurant->contact_number ?? '—' }}
                    </div>
                    <div class="mb-3">
                        <strong>Opening Time:</strong>
                        {{ $restaurant->opening_time ? date('h:i A', strtotime($restaurant->opening_time)) : '—' }} <br>
                        <strong>Closing Time:</strong>
                        {{ $restaurant->closing_time ? date('h:i A', strtotime($restaurant->closing_time)) : '—' }}
                    </div>
                </div>

                <hr>

                {{-- LOCATION --}}
                <h6 class="fw-bold">Location</h6>
                <div class="mb-2">
                    <strong>Latitude:</strong> {{ $restaurant->latitude }} <br>
                    <strong>Longitude:</strong> {{ $restaurant->longitude }}
                </div>

                <a href="https://www.google.com/maps?q={{ $restaurant->latitude }},{{ $restaurant->longitude }}"
                    target="_blank" class="btn btn-sm btn-outline-primary mb-3">
                    View on Google Maps
                </a>

                {{-- EMBED MAP --}}
                @if ($restaurant->latitude && $restaurant->longitude)
                    <div class="mb-4">
                        <iframe width="100%" height="300" style="border-radius:8px;border:0" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q={{ $restaurant->latitude }},{{ $restaurant->longitude }}&output=embed">
                        </iframe>
                    </div>
                @endif

                <hr>

                {{-- MENU IMAGE --}}
                <h6 class="fw-bold">Menu Image</h6>
                @if ($restaurant->menu_image)
                    <img src="{{ $restaurant->menu_image_url }}" width="200" style="border-radius:8px" class="mb-4">
                @else
                    <p class="text-muted">No menu image provided</p>
                @endif

                <hr>

                {{-- VIDEO --}}
                <h6 class="fw-bold">Video</h6>
                @if ($restaurant->video)
                    <video controls width="320" class="mb-4">
                        <source src="{{ $restaurant->video_url }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                @else
                    <p class="text-muted">No video provided</p>
                @endif

                <hr>

                {{-- IMAGES --}}
                <h6 class="fw-bold">Restaurant Images</h6>
                <div class="d-flex gap-2 flex-wrap mb-4">
                    @foreach ($restaurant->images as $image)
                        <img src="{{ $image->image_url }}" width="120" height="120"
                            style="object-fit:cover;border-radius:8px;">
                    @endforeach
                </div>

                <hr>

                {{-- ACTIONS --}}
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.restaurants.approve', $restaurant) }}">
                        @csrf
                        <button class="btn btn-success">
                            Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.restaurants.reject', $restaurant) }}">
                        @csrf
                        {{-- <input type="text"
                           name="admin_remark"
                           class="form-control mb-2"
                           placeholder="Reject reason"
                           required> --}}

                        <button class="btn btn-danger">
                            Reject
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

@endsection
