@extends('admin.layouts.app')

@section('title', 'Edit Member Profile')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">Edit Member Profile</h2>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $member->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $member->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $member->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="blocked" {{ old('status', $member->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Member Category</label>
                                <select name="member_category_id" class="form-select @error('member_category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('member_category_id', $member->memberProfile->member_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Associated Place</label>
                                <input type="text" class="form-control" value="{{ $member->memberProfile->place->name ?? 'N/A' }}" disabled>
                                <div class="form-text">Place can only be changed by the member.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control @error('profile_picture') is-invalid @enderror">
                            <div class="form-text">Leave empty to keep existing picture. Max size 2MB.</div>
                            @error('profile_picture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success py-2">
                                <i class="fas fa-save me-2"></i>Update Member Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <h5 class="card-title mb-4">Member Info</h5>
                    <div class="mb-3">
                        @if($member->profile_picture)
                            <img src="{{ asset('storage/' . $member->profile_picture) }}" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 64px;">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h4>{{ $member->name }}</h4>
                    <p class="text-muted">{{ $member->phone }}</p>
                    <div class="badge bg-info mb-2 px-3 py-2">
                        {{ $member->memberProfile->category->name }}
                    </div>
                    <br>
                    <div class="badge {{ $member->status === 'active' ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                        {{ ucfirst($member->status) }}
                    </div>
                    <hr>
                    <div class="text-start">
                        <p><strong>Member Since:</strong> {{ $member->created_at->format('d M, Y') }}</p>
                        <p><strong>Unique ID:</strong> {{ $member->unique_id ?? 'N/A' }}</p>
                        <p><strong>KYC Status:</strong> 
                            <span class="badge 
                                @if($member->memberProfile->kyc_status === 'approved') bg-success 
                                @elseif($member->memberProfile->kyc_status === 'rejected') bg-danger 
                                @else bg-warning @endif">
                                {{ ucfirst($member->memberProfile->kyc_status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
