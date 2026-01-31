@extends('admin.layouts.app')

@section('title', 'Edit Yateems Help')

@section('content')

<style>
    .required-label::after {
        content: " *";
        color: red;
        font-weight: bold;
    }

    .preview-img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
</style>

<div class="mb-3">
    <a href="{{ route('yateems-helps.show', $yateemsHelp) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h4 class="fw-bold text-success mb-4">Edit Yateems Help</h4>

        <form action="{{ route('yateems-helps.update', $yateemsHelp) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">

                <!-- Title -->
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Title</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $yateemsHelp->title) }}"
                           required>
                </div>

                <!-- Video -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Replace Video</label>
                    <input type="file"
                           name="video"
                           class="form-control"
                           accept="video/*">

                    @if ($yateemsHelp->video)
                        <small class="text-muted d-block mt-1">
                            Current video exists
                        </small>
                    @endif
                </div>

                <!-- Description -->
                <div class="col-md-12 mb-3">
                    <label class="form-label required-label">Description</label>
                    <textarea name="description"
                              class="form-control"
                              rows="4"
                              required>{{ old('description', $yateemsHelp->description) }}</textarea>
                </div>

                <!-- Existing Images -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Existing Images</label>
                    <div class="d-flex flex-wrap gap-3">
                        @forelse ($yateemsHelp->images as $image)
                            <img src="{{ asset('storage/' . $image->image) }}"
                                 class="preview-img">
                        @empty
                            <span class="text-muted">No images</span>
                        @endforelse
                    </div>
                </div>

                <!-- Add More Images -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Add More Images</label>
                    <input type="file"
                           name="images[]"
                           class="form-control"
                           multiple
                           accept="image/*">
                </div>

                <hr class="my-4">

                <!-- Bank Name -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Bank Name</label>
                    <input type="text"
                           name="bank_name"
                           class="form-control"
                           value="{{ old('bank_name', $yateemsHelp->bank_name) }}"
                           required>
                </div>

                <!-- IFSC -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">IFSC Code</label>
                    <input type="text"
                           name="ifsc_code"
                           class="form-control"
                           value="{{ old('ifsc_code', $yateemsHelp->ifsc_code) }}"
                           required>
                </div>

                <!-- Account No -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Account Number</label>
                    <input type="text"
                           name="account_no"
                           class="form-control"
                           value="{{ old('account_no', $yateemsHelp->account_no) }}"
                           required>
                </div>

                <!-- UPI -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">UPI ID</label>
                    <input type="text"
                           name="upi_id"
                           class="form-control"
                           value="{{ old('upi_id', $yateemsHelp->upi_id) }}">
                </div>

                <!-- QR Code -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Replace QR Code</label>
                    <input type="file"
                           name="qr_code"
                           class="form-control"
                           accept="image/*">

                    @if ($yateemsHelp->qr_code)
                        <small class="text-muted d-block mt-1">
                            Current QR code exists
                        </small>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('yateems-helps.index') }}"
                   class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
