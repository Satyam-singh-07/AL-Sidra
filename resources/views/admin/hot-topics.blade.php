@extends('admin.layouts.app')

@section('title', 'Hot Topics')

@section('content')

    <style>
        .topic-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .updates-count {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .action-buttons .btn:last-child {
            margin-right: 0;
        }
        .description-text {
            font-size: 0.9rem;
            color: #6c757d;
            display: -webkit-box;
            /* -webkit-line-clamp: 2; */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .trending-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
    </style>

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-success fw-bold mb-1">Hot Topics</h2>
                        {{-- <p class="text-muted mb-0">Manage trending topics and their updates</p> --}}
                    </div>
                    <a href="{{route('hot-topics.create')}}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> Add New Topic
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Topics</h6>
                                        <h3 class="mb-0">{{ $totalTopics }}</h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-fire text-primary fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Active Topics</h6>
                                        <h3 class="mb-0">{{ $activeTopics }}</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Updates</h6>
                                        <h3 class="mb-0">{{ $totalUpdates }}</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-sync-alt text-info fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Today's Views</h6>
                                        <h3 class="mb-0">{{ number_format($todayViews) }}</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-eye text-warning fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <!-- Search and Filters -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('hot-topics.index') }}">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="search"
                                            value="{{ request('search') }}"
                                            class="form-control border-start-0"
                                            placeholder="Search topics by title or description..."
                                        >
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <select name="sort" class="form-select" onchange="this.form.submit()">
                                        <option value="">Sort by: Latest Added</option>
                                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>
                                            Most Popular
                                        </option>
                                        <option value="updates" {{ request('sort') === 'updates' ? 'selected' : '' }}>
                                            Most Updates
                                        </option>
                                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                                            Oldest First
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Hot Topics Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">Sr no</th>
                                        <th>Topic Details</th>
                                        <th>Description</th>
                                        <th>Updates</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                              <tbody>
                                    @forelse ($topics as $topic)
                                        <tr>
                                            {{-- ID --}}
                                            <td class="fw-bold">
                                                {{ $loop->iteration }}
                                            </td>

                                            {{-- Topic Details --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="position-relative">
                                                        <img
                                                            src="{{ $topic->image_url }}"
                                                            alt="Topic"
                                                            class="topic-img me-3"
                                                        >

                                                        @if ($topic->is_trending)
                                                            <span class="badge bg-danger trending-badge">TRENDING</span>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <h6 class="mb-1">{{ $topic->title }}</h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            {{ $topic->created_at->format('M d, Y') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Description --}}
                                            <td>
                                                <p class="description-text mb-0">
                                                    {{ Str::limit($topic->description, 120) }}
                                                </p>
                                            </td>

                                            {{-- Updates Count --}}
                                            <td>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 updates-count">
                                                    <i class="fas fa-sync-alt me-1"></i>
                                                    {{ $topic->updates_count }} Updates
                                                </span>
                                            </td>

                                            {{-- Created --}}
                                            <td>
                                                <small>{{ $topic->created_at->format('M d, Y') }}</small><br>
                                                {{-- <small class="text-muted">by Admin</small> --}}
                                            </td>

                                           <td>
                                                <form action="{{ route('hot-topics.toggle-status', $topic->id) }}"
                                                    method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="badge border-0
                                                            {{ $topic->status === 'published' ? 'bg-success' : 'bg-secondary' }}"
                                                            style="cursor: pointer;">
                                                        {{ ucfirst($topic->status) }}
                                                    </button>
                                                </form>
                                            </td>

                                            {{-- Actions --}}
                                            <td class="action-buttons">
                                                <a href=""
                                                class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-list me-1"></i> Updates
                                                </a>

                                                <a href="{{ route('hot-topics.edit', $topic->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="{{ route('hot-topics.destroy', $topic->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this topic and all updates?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No hot topics found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        <!-- Pagination -->
                            @include('admin.partials.pagination', ['paginator' => $topics])
                    </div>
                </div>


                 <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this topic? All associated updates will also be deleted.</p>
                    <div class="alert alert-warning">
                        <strong>Topic:</strong> Ramadan 2025: Moon Sighting<br>
                        <strong>ID:</strong> #HT001<br>
                        <strong>Updates:</strong> 4 updates will be deleted
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete Topic</button>
                </div>
            </div>
        </div>
    </div>

@endsection