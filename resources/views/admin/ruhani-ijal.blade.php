@extends('admin.layouts.app')

@section('title', 'Ruhani Ijal Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Ruhani Ijal Management</h2>
            <p class="text-muted mb-0">Manage Aamil registrations and spiritual healing categories</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ruhani-ijal-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-2"></i> Categories
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <form method="GET" action="{{ route('ruhani-ijal.index') }}" class="row g-3 align-items-center mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by name or phone">
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Sr No</th>
                            <th>User Details</th>
                            <th>Category</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th width="100">Actions</th>
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
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $aamil->category->name }}
                                    </span>
                                </td>
                                <td>{{ $aamil->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($aamil->status == 'approved') bg-success 
                                        @elseif($aamil->status == 'pending') bg-warning text-dark 
                                        @else bg-danger @endif">
                                        {{ ucfirst($aamil->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('ruhani-ijal-aamils.show', $aamil->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No registrations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
