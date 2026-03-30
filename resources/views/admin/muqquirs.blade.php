@extends('admin.layouts.app')

@section('title', 'Muqquir Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Muqquir Management</h2>
            <p class="text-muted mb-0">Manage Muqquir registrations and verification</p>
        </div>
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
                            <th width="80">Sr No</th>
                            <th>User Details</th>
                            <th>Travel Fee</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($muqquirs as $muqquir)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $muqquir->user->name }}</div>
                                    <small class="text-muted">{{ $muqquir->user->phone }}</small>
                                </td>
                                <td>{{ $muqquir->travel_fee ?? 'N/A' }}</td>
                                <td>{{ $muqquir->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($muqquir->status == 'approved') bg-success 
                                        @elseif($muqquir->status == 'pending') bg-warning text-dark 
                                        @else bg-danger @endif">
                                        {{ ucfirst($muqquir->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.muqquirs.show', $muqquir->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($muqquir->status == 'pending')
                                            <form action="{{ route('admin.muqquirs.approve', $muqquir->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this muqquir?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.muqquirs.reject', $muqquir->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this muqquir?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
