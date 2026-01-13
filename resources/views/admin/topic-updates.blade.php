@extends('admin.layouts.app')

@section('title', 'Topic Updates')

@section('content')

    <style>
        .update-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s;
        }
        .update-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .update-number {
            width: 40px;
            height: 40px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .update-content {
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .topic-header {
            background: linear-gradient(135deg, #198754 0%, #0d6efd 100%);
            color: white;
            border-radius: 8px;
        }
    </style>


                <!-- Topic Header -->
                <div class="topic-header p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img
                                src="{{ $hotTopic->image_url }}"
                                alt="Topic"
                                class="img-fluid rounded"
                                style="border: 3px solid white;"
                            >
                        </div>

                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="mb-2">{{ $hotTopic->title }}</h3>

                                    <p class="mb-0 opacity-75">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Created: {{ $hotTopic->created_at->format('M d, Y') }}
                                        |
                                        <i class="fas fa-sync-alt ms-3 me-1"></i>
                                        {{ $updates->total() }} Updates
                                    </p>
                                </div>

                                @if ($hotTopic->is_trending)
                                    <span class="badge bg-warning fs-6">TRENDING</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="text-success fw-bold mb-1">Topic Updates</h4>
                        <p class="text-muted mb-0">Manage updates for this topic</p>
                    </div>
                    <div>
                        <a href="{{ route('hot-topics.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i> Back to Topics
                        </a>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                            <i class="fas fa-plus me-2"></i> Add New Update
                        </button>
                    </div>
                </div>

                <!-- Updates List -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-list-ol me-2"></i>All Updates ({{ $updates->total() }})</h5>
                        
                        @forelse ($updates as $index => $update)
                            <div class="update-card card mb-3">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="update-number me-3">
                                            {{ $updates->firstItem() + $index }}
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">{{ $update->title }}</h6>

                                                <small class="text-muted">
                                                    {{ $update->created_at->format('M d, Y | h:i A') }}
                                                </small>
                                            </div>

                                            <div class="update-content">
                                                {{ $update->content }}
                                            </div>

                                            <div class="mt-3">
                                                <!-- Edit -->
                                                <button
                                                    class="btn btn-sm btn-outline-primary me-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUpdateModal{{ $update->id }}">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>

                                                <!-- Delete -->
                                                <form
                                                    action="{{ route('hot-topics.topic-updates.destroy', [$hotTopic->id, $update->id]) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this update?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Edit Update Modal -->
                            <div class="modal fade" id="editUpdateModal{{ $update->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>Edit Update
                                            </h5>
                                            <button type="button"
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <form
                                                action="{{ route('hot-topics.topic-updates.update', [$hotTopic->id, $update->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="mb-3">
                                                    <label class="form-label">Update Title *</label>
                                                    <input
                                                        type="text"
                                                        name="title"
                                                        class="form-control"
                                                        value="{{ old('title', $update->title) }}"
                                                        required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Update Content *</label>
                                                    <textarea
                                                        name="content"
                                                        class="form-control"
                                                        rows="6"
                                                        required>{{ old('content', $update->content) }}</textarea>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button"
                                                            class="btn btn-secondary"
                                                            data-bs-dismiss="modal">
                                                        Cancel
                                                    </button>

                                                    <button type="submit"
                                                            class="btn btn-primary">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                No updates added yet.
                            </div>
                        @endforelse
                            @include('admin.partials.pagination', ['paginator' => $updates])
                    </div>
                </div>


    <!-- Add Update Modal -->
<div class="modal fade" id="addUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Add New Update
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form
                    action="{{ route('hot-topics.topic-updates.store', $hotTopic->id) }}"
                    method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Update Title *</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            placeholder="e.g., Update – Latest Developments"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Update Content *</label>
                        <textarea
                            name="content"
                            class="form-control"
                            rows="6"
                            placeholder="Enter detailed update content..."
                            required></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-success">
                            Publish Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Delete Update Modal -->
    <div class="modal fade" id="deleteUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this update?</p>
                    <div class="alert alert-warning">
                        <strong>Update:</strong> Moon Sighting Reports<br>
                        <strong>Date:</strong> Mar 1, 2025<br>
                        <strong>Update #:</strong> 1
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection