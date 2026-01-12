@extends('admin.layouts.app')

@section('title', 'Edit Hot Topic')

@section('content')

<style>
    .image-preview {
        width: 200px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
        display: block;
    }
    .upload-area {
        border: 2px dashed #0d6efd;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .upload-area:hover {
        border-color: #0a58ca;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-success fw-bold mb-1">Edit Hot Topic</h2>
        <p class="text-muted mb-0">Update topic details, image and video</p>
    </div>
    <a href="{{ route('hot-topics.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Topics
    </a>
</div>

<!-- Topic Form -->
<div class="card shadow-sm border-0 form-container">
    <div class="card-body p-4">

        <form action="{{ route('hot-topics.update', $hotTopic->id) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Image Upload Section -->
            <div class="mb-4">
                <label class="form-label fw-bold">Topic Image</label>

                <div class="upload-area mb-3"
                     onclick="document.getElementById('imageInput').click()">
                    <div class="mb-3">
                        <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Click to replace image</h5>
                    <p class="text-muted mb-0">JPG, PNG or GIF (Max: 2MB)</p>

                    <input type="file"
                           name="image"
                           id="imageInput"
                           accept="image/*"
                           style="display: none;"
                           onchange="previewImage(event)">
                </div>

                <div class="text-center">
                    <img id="imagePreview"
                         src="{{ $hotTopic->image_url }}"
                         class="image-preview"
                         alt="Topic Image">
                </div>
            </div>

            <!-- Video Upload Section -->
            <div class="mb-4">
                <label class="form-label fw-bold">Topic Video</label>

                @if ($hotTopic->video_url)
                    <div class="mb-2">
                        <video width="100%" height="240" controls>
                            <source src="{{ $hotTopic->video_url }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @endif

                <input type="file"
                       class="form-control"
                       name="video"
                       accept="video/mp4,video/webm,video/ogg">

                <div class="form-text">
                    Upload a new video to replace the existing one (Max 20MB)
                </div>
            </div>

            <!-- Title -->
            <div class="mb-4">
                <label class="form-label fw-bold">Topic Title *</label>
                <input type="text"
                       name="title"
                       class="form-control"
                       value="{{ old('title', $hotTopic->title) }}"
                       required>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-bold">Topic Description *</label>
                <textarea name="description"
                          class="form-control"
                          rows="6"
                          required>{{ old('description', $hotTopic->description) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between pt-3">
                <a href="{{ route('hot-topics.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Update Topic
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

@endsection
