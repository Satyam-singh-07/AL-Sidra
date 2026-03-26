@extends('admin.layouts.app')

@section('title', 'Restaurants')

@section('content')

    <style>
        .restaurant-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .status-badge {
            cursor: pointer;
            transition: all 0.2s;
        }

        .status-badge:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Restaurants</h2>
            <p class="text-muted mb-0">Manage restaurants in the system</p>
        </div>
        <div>
            <a href="{{ route('restaurants.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Add New Restaurant
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Restaurant</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
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
                                        @else
                                            <div class="restaurant-img me-3 bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-utensils text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $restaurant->name }}</h6>
                                            <small class="text-muted">
                                                {{ Str::limit($restaurant->description, 40) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ Str::limit($restaurant->address, 50) }}</td>
                                <td>{{ $restaurant->contact_number }}</td>

                                <td>
                                    <span class="badge status-badge {{ $restaurant->status == 'approved' ? 'bg-success' : ($restaurant->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}"
                                          onclick="cycleStatus({{ $restaurant->id }}, this)">
                                        {{ ucfirst($restaurant->status) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('restaurants.show', $restaurant) }}"
                                            class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('restaurants.edit', $restaurant) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this restaurant?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No restaurants found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $restaurants])
            </div>
        </div>
    </div>

    <script>
        function cycleStatus(id, element) {
            fetch(`/admin/restaurants/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                const status = data.status;
                element.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                
                element.className = 'badge status-badge';
                if (status === 'approved') element.classList.add('bg-success');
                else if (status === 'pending') element.classList.add('bg-warning', 'text-dark');
                else element.classList.add('bg-danger');
            });
        }
    </script>

@endsection
