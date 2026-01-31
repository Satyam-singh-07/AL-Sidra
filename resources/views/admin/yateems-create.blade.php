@extends('admin.layouts.app')

@section('title', 'Add Yateems Help')

@section('content')

    <style>
        .required-label::after {
            content: " *";
            color: red;
            font-weight: bold;
        }
    </style>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="text-success fw-bold mb-4">Add Yateems Help</h4>

            <form action="{{ route('yateems-helps.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Title -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                        </select>
                    </div>

                    <!-- Video -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Video</label>
                        <input type="file" name="video" class="form-control" accept="video/*">
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label required-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                    </div>

                    <!-- Multiple Images -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label required-label">Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                    </div>

                    <hr class="my-4">

                    <!-- Bank Name -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required>
                    </div>

                    <!-- IFSC -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code') }}" required>
                    </div>

                    <!-- Account Number -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label required-label">Account Number</label>
                        <input type="text" name="account_no" class="form-control" value="{{ old('account_no') }}"
                            required>
                    </div>

                    <!-- UPI ID -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control" value="{{ old('upi_id') }}">
                    </div>

                    <!-- QR Code -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">QR Code</label>
                        <input type="file" name="qr_code" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('yateems-helps.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
