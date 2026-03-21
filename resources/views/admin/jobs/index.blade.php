@extends('admin.layouts.app')

@section('title', 'Jobs Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Jobs Management</h2>
            <p class="text-muted mb-0">Approve and manage job openings posted by Mutvallis</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('job-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-2"></i> Categories
            </a>
        </div>
    </div>

    @if (session('success'))
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
                            <th>Job Details</th>
                            <th>Category</th>
                            <th>Posted By</th>
                            <th>Place</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $job->title }}</div>
                                    <small class="text-muted">{{ Str::limit($job->description, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ $job->category->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $job->user->name }}</div>
                                    <small class="text-muted">{{ $job->user->phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        {{ ucfirst($job->place_type) }}: {{ $job->place->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge 
                                        @if ($job->status == 'approved' || $job->status == 'active') bg-success 
                                        @elseif($job->status == 'pending') bg-warning text-dark 
                                        @elseif($job->status == 'rejected') bg-danger 
                                        @else bg-secondary @endif">
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#viewModal{{ $job->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if ($job->status == 'pending')
                                            <form action="{{ route('admin.jobs.approve', $job->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Approve this job?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.jobs.reject', $job->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Reject this job?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('jobs.destroy', $job->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-dark"
                                                onclick="return confirm('Delete this job?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- View Modal --}}
                            <div class="modal fade" id="viewModal{{ $job->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Job Details: {{ $job->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Category</label>
                                                    <p>{{ $job->category->name }} ({{ ucfirst($job->category->type) }})</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Status</label>
                                                    <p>{{ ucfirst($job->status) }}</p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="fw-bold text-muted small d-block">Description</label>
                                                    <p>{{ $job->description }}</p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="fw-bold text-muted small d-block">Requirements</label>
                                                    <p>{{ $job->requirements ?: 'Not specified' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Salary Range</label>
                                                    <p>{{ $job->salary_range ?: 'Not specified' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Place</label>
                                                    <p>{{ ucfirst($job->place_type) }}: {{ $job->place->name ?? 'N/A' }}</p>
                                                </div>
                                                <hr>
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Contact Person</label>
                                                    <p>{{ $job->contact_person ?: $job->user->name }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="fw-bold text-muted small d-block">Contact Phone</label>
                                                    <p>{{ $job->contact_phone ?: $job->user->phone }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No job openings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
