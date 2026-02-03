@extends('admin.layouts.app')

@section('title', 'Madarsa Management')

@section('content')

    <style>
        .madarsa-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
        }

        .gender-badge {
            font-size: 0.7rem;
        }

        .location-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Madarsa Listings</h2>
            <p class="text-muted mb-0">Manage all registered madarsas</p>
        </div>
        <a href="{{ route('madarsas.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i> Add New Madarsa
        </a>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by name or address">
        </div>

        <div class="col-md-3">
            <select name="community" class="form-select">
                <option value="">All Communities</option>
                @foreach ($communities as $community)
                    <option value="{{ $community->id }}" {{ request('community') == $community->id ? 'selected' : '' }}>
                        {{ $community->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-success w-100">Filter</button>
            <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Madarsa Details</th>
                        <th>Location</th>
                        <th>Community</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($madarsas as $madarsa)
                        <tr>
                            <td class="fw-bold">#{{ $loop->iteration }}</td>

                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($madarsa->images->first())
                                        <img src="{{ asset('storage/' . $madarsa->images->first()->image_path) }}"
                                            class="madarsa-img me-3">
                                    @endif
                                    <div>
                                        <h6 class="mb-1">{{ $madarsa->name }}</h6>
                                        <span class="badge bg-secondary gender-badge">
                                            {{ ucfirst($madarsa->gender) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ Str::limit($madarsa->address, 40) }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td class="location-info">
                                Lat: {{ $madarsa->latitude }},
                                Lng: {{ $madarsa->longitude }}
                            </td>

                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    {{ $madarsa->community->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="badge status-badge
        @if ($madarsa->status == 'active') bg-success
        @elseif($madarsa->status == 'pending') bg-warning text-dark
        @else bg-danger @endif"
                                    data-id="{{ $madarsa->id }}" style="cursor:pointer;">
                                    {{ ucfirst($madarsa->status) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('madarsas.edit', $madarsa) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="{{ route('madarsas.show', $madarsa) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <form action="{{ route('madarsas.destroy', $madarsa) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this madarsa?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No madarsas found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $madarsas])
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('status-badge')) return;

            const badge = e.target;
            const madarsaId = badge.dataset.id;

            fetch(`/admin/madarsas/${madarsaId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    badge.textContent =
                        data.status.charAt(0).toUpperCase() + data.status.slice(1);

                    badge.classList.remove(
                        'bg-success',
                        'bg-warning',
                        'bg-danger',
                        'text-dark'
                    );

                    if (data.status === 'active') {
                        badge.classList.add('bg-success');
                    } else if (data.status === 'pending') {
                        badge.classList.add('bg-warning', 'text-dark');
                    } else {
                        badge.classList.add('bg-danger');
                    }
                });
        });
    </script>

@endsection
