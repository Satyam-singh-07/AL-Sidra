@extends('admin.layouts.app')

@section('title', 'Aamil Registrations')

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
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Aamil Registrations</h2>
            <p class="text-muted mb-0">Manage and verify Aamil applications for Ruhani Ijal</p>
        </div>
        <a href="{{ route('ruhani-ijal.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

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
                                                <form action="{{ route('admin.ruhani-ijal-aamils.approve', $aamil->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Approve Application</button>
                                                </form>
                                                <form action="{{ route('admin.ruhani-ijal-aamils.reject', $aamil->id) }}" method="POST">
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
