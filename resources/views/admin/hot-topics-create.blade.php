@extends('admin.layouts.app')

@section('title', 'Add Muslim Updates')

@section('content')

    <style>
        .image-preview {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            display: none;
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
                        <h2 class="text-success fw-bold mb-1">Add New Hot Topic</h2>
                        <p class="text-muted mb-0">Create a new trending topic with image, title and description</p>
                    </div>
                    <a href="{{route('hot-topics.index')}}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Topics
                    </a>
                </div>

                <!-- Topic Form -->
                <div class="card shadow-sm border-0 form-container">
                    <div class="card-body p-4">
                        <form action="{{route('hot-topics.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <!-- Image Upload Section -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Topic Image</label>
                                <div class="upload-area mb-3" onclick="document.getElementById('imageInput').click()">
                                    <div class="mb-3">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                    </div>
                                    <h5>Click to upload image</h5>
                                    <p class="text-muted mb-0">JPG, PNG or GIF (Max: 2MB)</p>
                                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                </div>
                                <div class="text-center">
                                    <img id="imagePreview" class="image-preview" alt="Preview">
                                </div>
                            </div>

                            <!-- Video Upload Section (Optional) -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Topic Video (Optional)</label>
                                <input
                                    type="file"
                                    class="form-control"
                                    name="video"
                                    accept="video/mp4,video/webm,video/ogg"
                                >
                                <div class="form-text">
                                    Optional. Upload one video only. MP4 / WebM / OGG. Max 20MB.
                                </div>
                            </div>

                            <!-- Title Field -->
                            <div class="mb-4">
                                <label for="topicTitle" class="form-label fw-bold">Topic Title *</label>
                                <input type="text" name="title" class="form-control" id="topicTitle" placeholder="Enter topic title" required>
                                <div class="form-text">Keep it clear and descriptive (max 100 characters)</div>
                            </div>

                            <!-- Description Field -->
                            <div class="mb-4">
                                <label for="topicDescription" class="form-label fw-bold">Topic Description *</label>
                                <textarea class="form-control" name="description" id="topicDescription" rows="6" placeholder="Enter detailed description of the topic" required></textarea>
                                <div class="form-text">Provide comprehensive details about this trending topic</div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between pt-3">
                                <a href="{{route('hot-topics.index')}}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                                <div>
                                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-save me-2"></i> Save as Draft
                                    </button>
                                    <button type="submit" name="action" value="publish" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-2"></i> Publish Topic
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <script>
        // Simple image preview function
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Sidebar injection
        document.addEventListener("DOMContentLoaded", function () {
            loadLayout();
            highlightCurrentPage();
            
            // Add active class to Muslim Updates in sidebar
            const hotTopicsLink = document.querySelector('a[href="hot-topics.html"]');
            if (hotTopicsLink) {
                hotTopicsLink.classList.add('active');
            }
        });
    </script>

@endsection