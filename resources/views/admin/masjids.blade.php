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

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
    <form method="GET" class="row g-3">
        <div class="col-md-6">
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

        <div class="col-12">
            <button class="btn btn-success">Filter</button>
            <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                                        class="badge 
            @if ($masjid->status == 'active') bg-success
            @elseif($masjid->status == 'pending') bg-warning text-dark
            @else bg-danger @endif">
                                        {{ ucfirst($masjid->status) }}
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
                                            onclick="return confirm('Delete this masjid?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
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

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">
                        <i class="fas fa-mosque me-2"></i>Masjid Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="https://via.placeholder.com/200" alt="Masjid" class="img-fluid rounded">
                            <h5 class="mt-3">Jamia Masjid Al-Noor</h5>
                            <span class="badge bg-success">Verified</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Masjid ID</label>
                                    <p class="fw-bold">#001</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Community</label>
                                    <p><span class="badge bg-success">Sunni</span></p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Address</label>
                                    <p>Sector 62, Noida, Uttar Pradesh - 201301</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Location</label>
                                    <p>Lat: 28.6274, Long: 77.3760</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Registration Date</label>
                                    <p>2024-12-15</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Donation Passbook</label>
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <p class="mb-1">donation_passbook_001.pdf</p>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="masjid-edit.html?id=001" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this masjid? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <strong>Masjid:</strong> Jamia Masjid Al-Noor<br>
                        <strong>ID:</strong> #001<br>
                        <strong>Location:</strong> Sector 62, Noida
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete Masjid</button>
                </div>
            </div>
        </div>
    </div>

@endsection
