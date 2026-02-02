@extends('admin.layouts.app')

@section('title', 'Restaurant Requests')

@section('content')

    <style>
        .restaurant-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Restaurant Requests</h2>
            <p class="text-muted mb-0">Pending restaurant submissions</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Restaurant</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($restaurants as $restaurant)
                            <tr>
                                <td class="fw-bold">#{{ $loop->iteration }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($restaurant->firstImage)
                                            <img src="{{ $restaurant->firstImage->image_url }}" class="restaurant-img me-3">
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $restaurant->name }}</h6>
                                            <small class="text-muted">
                                                {{ Str::limit($restaurant->description, 50) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $restaurant->address }}</td>

                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                    ];
                                @endphp

                                <td>
                                    <span class="badge {{ $statusClasses[$restaurant->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst($restaurant->status) }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('restaurants.show', $restaurant) }}"
                                        class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No pending restaurant requests
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $restaurants->links() }}
            </div>
        </div>
    </div>

@endsection
