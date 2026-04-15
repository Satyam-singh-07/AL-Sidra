@extends('admin.layouts.app')

@section('title', 'Masjid Management')

@section('content')

    <style>
        .masjid-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .status-badge, .kyc-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
        }

        .action-buttons .btn {
            margin-right: 5px;
        }

        .action-buttons .btn:last-child {
            margin-right: 0;
        }

        .search-box {
            max-width: 300px;
        }

        .location-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Masjid Listings</h2>
            <p class="text-muted mb-0">Manage all Islamic centers, mosques, and madarsas</p>
        </div>
        <a href="{{ route('masjids.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i> Add New Masjid
        </a>
    </div>

    <!-- Search and Filters -->
    <form method="GET" class="row g-3 align-items-center mb-4">
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
            <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>


    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Masjids</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-mosque text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active</h6>
                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending</h6>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Inactive</h6>
                            <h3 class="mb-0">{{ $stats['inactive'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-users text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Masjids Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Masjid Details</th>
                            <th>Location</th>
                            <th>Community</th>
                            <th>Status</th>
                            <th>KYC Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masjids as $masjid)
                            <tr>
                                <td class="fw-bold">#{{ $loop->iteration }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($masjid->images->first())
                                            <img src="{{ asset('storage/' . $masjid->images->first()->image_path) }}"
                                                class="masjid-img me-3">
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $masjid->name }}</h6>
                                            @if($masjid->unique_id)
                                                <div class="mb-1"><small class="text-success fw-bold">{{ $masjid->unique_id }}</small></div>
                                            @endif
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($masjid->address, 40) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="location-info">
                                        <div>Lat: {{ $masjid->latitude }}, Lng: {{ $masjid->longitude }}</div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $masjid->community->name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge status-badge 
        @if ($masjid->status == 'active') bg-success
        @elseif($masjid->status == 'pending') bg-warning text-dark
        @else bg-danger @endif"
                                        data-id="{{ $masjid->id }}">
                                        {{ ucfirst($masjid->status) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge kyc-status-badge 
        @if ($masjid->kyc_status == 'active') bg-success
        @else bg-danger @endif"
                                        data-id="{{ $masjid->id }}">
                                        {{ ucfirst($masjid->kyc_status) }}
                                    </span>
                                </td>

                                <td class="action-buttons">
                                    <a href="{{ route('masjids.edit', $masjid) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('masjids.show', $masjid) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <form action="{{ route('masjids.destroy', $masjid) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this masjid?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No masjids found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $masjids])
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('status-badge')) {
                const badge = e.target;
                const masjidId = badge.dataset.id;

                fetch(`/admin/masjids/${masjidId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(res => res.json())
                    .then(data => {
                        badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                        badge.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'text-dark');

                        if (data.status === 'active') {
                            badge.classList.add('bg-success');
                        } else if (data.status === 'pending') {
                            badge.classList.add('bg-warning', 'text-dark');
                        } else {
                            badge.classList.add('bg-danger');
                        }
                    });
            }

            if (e.target.classList.contains('kyc-status-badge')) {
                const badge = e.target;
                const masjidId = badge.dataset.id;

                fetch(`/admin/masjids/${masjidId}/kyc-status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(res => res.json())
                    .then(data => {
                        badge.textContent = data.kyc_status.charAt(0).toUpperCase() + data.kyc_status.slice(1);

                        badge.classList.remove('bg-success', 'bg-danger');

                        if (data.kyc_status === 'active') {
                            badge.classList.add('bg-success');
                        } else {
                            badge.classList.add('bg-danger');
                        }
                    });
            }
        });
    </script>

@endsection
