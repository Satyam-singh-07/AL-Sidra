@extends('admin.layouts.app')

@section('title', 'Muqquir Details')

@section('content')

    <div class="mb-4">
        <a href="{{ route('admin.muqquirs.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i> Back to List
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-success fw-bold mb-0">Muqquir Registration Details</h2>
            <div>
                <span class="badge px-3 py-2 
                    @if($muqquir->status == 'approved') bg-success 
                    @elseif($muqquir->status == 'pending') bg-warning text-dark 
                    @else bg-danger @endif">
                    {{ ucfirst($muqquir->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- User Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user me-2 text-success"></i> Requester Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Full Name</label>
                            <p class="fw-bold">{{ $muqquir->user->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Phone Number</label>
                            <p class="fw-bold">{{ $muqquir->user->phone }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Email Address</label>
                            <p class="fw-bold">{{ $muqquir->user->email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Address</label>
                            <p class="fw-bold">{{ $muqquir->user->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-success"></i> Application Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase">Description</label>
                        <p>{{ $muqquir->description ?? 'No description provided.' }}</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Bank Account No</label>
                            <p class="fw-bold">{{ $muqquir->account_no ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">IFSC Code</label>
                            <p class="fw-bold">{{ $muqquir->ifsc_code ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase">Travel Fee</label>
                            <p class="fw-bold">{{ $muqquir->travel_fee ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-video me-2 text-success"></i> Verification Videos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($muqquir->videos as $video)
                            <div class="col-md-6">
                                <video width="100%" height="auto" controls class="rounded shadow-sm">
                                    <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                No videos uploaded.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Action Card -->
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 2rem;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Actions</h5>
                </div>
                <div class="card-body">
                    @if($muqquir->status == 'pending')
                        <form action="{{ route('admin.muqquirs.approve', $muqquir->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this muqquir?')">
                                <i class="fas fa-check me-2"></i> Approve Application
                            </button>
                        </form>
                        <form action="{{ route('admin.muqquirs.reject', $muqquir->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this muqquir?')">
                                <i class="fas fa-times me-2"></i> Reject Application
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.muqquirs.destroy', $muqquir->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this registration?')">
                            <i class="fas fa-trash-alt me-2"></i> Delete Registration
                        </button>
                    </form>

                    <hr>
                    <div class="small text-muted">
                        <p class="mb-1"><strong>Applied on:</strong> {{ $muqquir->created_at->format('d M Y, h:i A') }}</p>
                        <p class="mb-0"><strong>Last updated:</strong> {{ $muqquir->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
