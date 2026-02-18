@extends('admin.layouts.app')

@section('title', 'Video Management')

@section('content')

    <style>
        /* Specific styles for Video management */
        .video-thumbnail {
            width: 160px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            background-color: #000;
        }

        .video-preview-container {
            width: 100%;
            height: 180px;
            background-color: #000;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .video-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .video-title {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-actions .btn {
            margin-right: 5px;
        }

        .table-actions .btn:last-child {
            margin-right: 0;
        }

        .video-controls {
            margin-top: 10px;
        }

        .video-controls button {
            margin-right: 5px;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">Video Management</h2>

        <div class="d-flex gap-2">
            <a href="{{ route('video-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-2"></i> Video Categories
            </a>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                <i class="fas fa-plus me-2"></i> Add Video
            </button>
        </div>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="videosTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Video</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($videos as $video)
                            @php
                                $videoUrl = asset('storage/' . $video->video_path);
                                $fileName = basename($video->video_path);
                            @endphp

                            <tr>
                                <td>{{ $loop->index + 1 }}</td>

                                <td>
                                    <video class="video-thumbnail" muted>
                                        <source src="{{ $videoUrl }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </td>

                                <td class="video-title" title="{{ $video->title }}">
                                    {{ $video->title }}
                                </td>

                                <td>
                                    {{ $video->category?->name ?? '—' }}
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        {{-- <span class="small text-muted">{{ $fileName }}</span> --}}

                                        <div class="video-controls">
                                            <a href="{{ asset('storage/' . $video->video_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary btn-play">
                                                Play
                                            </a>

                                            <a href="{{ $videoUrl }}" class="btn btn-sm btn-outline-info" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge {{ $video->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($video->status) }}
                                    </span>
                                </td>

                                <td class="table-actions">
                                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="{{ $video->id }}"
                                        data-title="{{ $video->title }}" data-category="{{ $video->video_category_id }}"
                                        data-status="{{ $video->status }}"
                                        data-video="{{ asset('storage/' . $video->video_path) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>


                                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $video->id }}"
                                        data-title="{{ $video->title }}" data-status="{{ $video->status }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No videos found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>


                </table>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div class="modal fade" id="addVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-success">Add New Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Video Title *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Video *</label>
                            <input type="file" class="form-control" name="video" accept="video/*" required>
                            <div class="form-text">
                                Supported formats: MP4, WebM, MOV (Max 50MB)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="video_category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="modal-footer px-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                Save Video
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>


    <!-- Edit Video Modal -->
    <div class="modal fade" id="editVideoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-primary">Edit Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="editVideoForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="id" id="editVideoId">

                        <div class="mb-3">
                            <label class="form-label">Video Title *</label>
                            <input type="text" class="form-control" name="title" id="editVideoTitle" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Video</label>
                            <div class="video-preview-container">
                                <video id="currentVideoPreview" class="video-preview" controls></video>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Change Video (Optional)</label>
                            <input type="file" class="form-control" name="video" id="editVideoFile"
                                accept="video/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="video_category_id" class="form-select" id="editVideoCategory">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="editVideoStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="modal-footer px-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                Update Video
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>


    <!-- Delete Video Modal -->
    <div class="modal fade" id="deleteVideoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="deleteVideoForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-body">
                        <p class="mb-0">
                            Are you sure you want to delete this video? This action cannot be undone.
                        </p>

                        <div class="alert alert-warning mt-3 mb-0">
                            <strong>Video ID:</strong> <span id="deleteVideoId"></span><br>
                            <strong>Title:</strong> <span id="deleteVideoTitle"></span><br>
                            <strong>Status:</strong> <span id="deleteVideoStatus"></span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Delete Video
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <script>
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {

                const id = this.dataset.id;
                const title = this.dataset.title;
                const status = this.dataset.status;
                const videoUrl = this.dataset.video;
                const category = this.dataset.category;

                const form = document.getElementById('editVideoForm');

                form.action = `/admin/videos/${id}`;

                document.getElementById('editVideoId').value = id;
                document.getElementById('editVideoTitle').value = title;
                document.getElementById('editVideoStatus').value = status;
                document.getElementById('currentVideoPreview').src = videoUrl;
                document.getElementById('editVideoCategory').value = category;

                const modal = new bootstrap.Modal(document.getElementById('editVideoModal'));
                modal.show();
            });
        });
    </script>
    <script>
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {

                const id = this.dataset.id;
                const title = this.dataset.title;
                const status = this.dataset.status;

                const form = document.getElementById('deleteVideoForm');

                form.action = `/admin/videos/${id}`;

                document.getElementById('deleteVideoId').innerText = id;
                document.getElementById('deleteVideoTitle').innerText = title;
                document.getElementById('deleteVideoStatus').innerText = status;

                const modal = new bootstrap.Modal(
                    document.getElementById('deleteVideoModal')
                );
                modal.show();
            });
        });
    </script>

@endsection
