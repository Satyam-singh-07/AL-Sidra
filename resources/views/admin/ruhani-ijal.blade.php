@extends('admin.layouts.app')

@section('title', 'Ruhani Ijal Management')

@section('content')

    <style>
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        .status-approved {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .action-btns .btn {
            margin-right: 5px;
        }
        .stats-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Ruhani Ijal Management</h2>
            <p class="text-muted mb-0">Review and manage Aamil registrations for spiritual healing</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ruhani-ijal-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-2"></i> Manage Categories
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Applied</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 stats-card">
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
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Approved</h6>
                            <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Rejected</h6>
                            <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <form method="GET" action="{{ route('ruhani-ijal.index') }}" class="row g-3 align-items-center mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by name or phone">
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-success w-100" type="submit">Filter</button>
            <a href="{{ route('ruhani-ijal.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Aamils Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>User Name</th>
                            <th>Category</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aamils as $aamil)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $aamil->user->name }}</div>
                                    <small class="text-muted">{{ $aamil->user->phone }}</small>
                                </td>
                                <td>{{ $aamil->category->name }}</td>
                                <td>{{ Str::limit($aamil->experience, 50) }}</td>
                                <td>
                                    <span class="status-badge status-{{ $aamil->status }}">
                                        {{ ucfirst($aamil->status) }}
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <button class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $aamil->id }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>

                                    @if($aamil->status === 'pending')
                                        <form action="{{ route('admin.ruhani-ijal-aamils.approve', $aamil->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Approve this Aamil?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.ruhani-ijal-aamils.reject', $aamil->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this Aamil?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            {{-- View Modal --}}
                            <div class="modal fade" id="viewModal{{ $aamil->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Aamil Registration Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h6>User Information</h6>
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <td class="text-muted" width="120">Name:</td>
                                                            <td class="fw-bold">{{ $aamil->user->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Phone:</td>
                                                            <td>{{ $aamil->user->phone }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Email:</td>
                                                            <td>{{ $aamil->user->email ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Address:</td>
                                                            <td>{{ $aamil->user->address ?? 'N/A' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Registration Details</h6>
                                                    <table class="table table-sm table-borderless">
                                                        <tr>
                                                            <td class="text-muted" width="120">Category:</td>
                                                            <td class="fw-bold text-success">{{ $aamil->category->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Status:</td>
                                                            <td>
                                                                <span class="status-badge status-{{ $aamil->status }}">
                                                                    {{ ucfirst($aamil->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Applied On:</td>
                                                            <td>{{ $aamil->created_at->format('d M Y, h:i A') }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <h6 class="fw-bold">Experience</h6>
                                                <div class="p-3 bg-light rounded border">
                                                    {{ $aamil->experience }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <h6 class="fw-bold">Description / Additional Notes</h6>
                                                <div class="p-3 bg-light rounded border">
                                                    {{ $aamil->description ?? 'No additional description provided.' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            
                                            @if($aamil->status === 'pending')
                                                <form action="{{ route('admin.ruhani-ijal-aamils.approve', $aamil->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Approve Application</button>
                                                </form>
                                                <form action="{{ route('admin.ruhani-ijal-aamils.reject', $aamil->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Reject Application</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No Aamil registrations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
