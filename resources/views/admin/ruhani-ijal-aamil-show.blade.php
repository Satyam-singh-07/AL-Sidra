@extends('admin.layouts.app')

@section('title', 'Aamil Registration Details')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Registration Details</h2>
            <p class="text-muted mb-0">Reviewing Aamil application for {{ $aamil->user->name }}</p>
        </div>
        <a href="{{ route('ruhani-ijal.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <!-- User Information Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-success fw-bold">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                        <h4 class="mt-3 fw-bold">{{ $aamil->user->name }}</h4>
                        <span class="badge 
                            @if($aamil->status == 'approved') bg-success 
                            @elseif($aamil->status == 'pending') bg-warning text-dark 
                            @else bg-danger @endif">
                            {{ ucfirst($aamil->status) }}
                        </span>
                    </div>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" width="100">Phone:</td>
                            <td class="fw-bold">{{ $aamil->user->phone }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $aamil->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address:</td>
                            <td>{{ $aamil->user->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Applied:</td>
                            <td>{{ $aamil->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Action Card (Only for pending) -->
            @if($aamil->status === 'pending')
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 text-success fw-bold">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.ruhani-ijal-aamils.approve', $aamil->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this Aamil?')">
                                    <i class="fas fa-check me-2"></i> Approve Application
                                </button>
                            </form>

                            <form action="{{ route('admin.ruhani-ijal-aamils.reject', $aamil->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this Aamil?')">
                                    <i class="fas fa-times me-2"></i> Reject Application
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-8">
            <!-- Registration Details Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-success fw-bold">Application Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-2">Selected Categories</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($aamil->categories as $category)
                                <div class="p-2 px-3 bg-info bg-opacity-10 text-info rounded border border-info border-opacity-25">
                                    <i class="fas fa-layer-group me-2"></i> {{ $category->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-2">Years of Experience / Background</h6>
                        <div class="p-3 bg-light rounded border">
                            {{ $aamil->experience }}
                        </div>
                    </div>

                    <div class="mb-0">
                        <h6 class="text-muted fw-bold mb-2">Detailed Description</h6>
                        <div class="p-3 bg-light rounded border" style="min-height: 200px; white-space: pre-wrap;">
                            {{ $aamil->description ?? 'No detailed description provided.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
