@extends('admin.layouts.app')

@section('title', 'Communities')

@section('content')
    <style>
        .community-stats { font-size: 0.85rem; color: #6c757d; }
        .member-count { font-weight: bold; color: #198754; }
        .table-actions .btn { margin-right: 5px; }
        .table-actions .btn:last-child { margin-right: 0; }
    </style>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-success fw-bold">Communities</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommunityModal">
                        <i class="fas fa-plus me-2"></i> Add Community
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <!-- <th>Members</th> -->
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($communities as $community)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $community->name }}</strong><br>
                                            <span class="community-stats">
                                                Registered: {{ $community->created_at->format('Y-m-d') }}
                                            </span>
                                        </td>
                                        <td>{{ $community->description ?? '-' }}</td>
                                        <td>
                                            <form method="POST"
                                                action="{{ route('communities.toggle-status', $community) }}"
                                                style="display:inline">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        class="badge border-0 {{ $community->status === 'active' ? 'bg-success' : 'bg-secondary' }}"
                                                        style="cursor:pointer">
                                                    {{ ucfirst($community->status) }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="table-actions">
                                            <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCommunityModal{{ $community->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteCommunityModal{{ $community->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="editCommunityModal{{ $community->id }}">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('communities.update', $community) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5>Edit Community</h5>
                                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <input type="text" name="name"
                                                            class="form-control mb-3"
                                                            value="{{ $community->name }}" required>

                                                        <textarea name="description"
                                                            class="form-control mb-3">{{ $community->description }}</textarea>

                                                        <select name="status" class="form-select">
                                                            <option value="active" @selected($community->status === 'active')>Active</option>
                                                            <option value="inactive" @selected($community->status === 'inactive')>Inactive</option>
                                                        </select>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-success">Update</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="deleteCommunityModal{{ $community->id }}">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('communities.destroy', $community) }}">
                                                @csrf
                                                @method('DELETE')

                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="text-danger">Confirm Delete</h5>
                                                    </div>

                                                    <div class="modal-body">
                                                        Delete <strong>{{ $community->name }}</strong>?
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-danger">Delete</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No communities found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Add Community Modal -->
                <div class="modal fade" id="addCommunityModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-success">Add New Community</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('communities.store') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Community Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-success">Save Community</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

@endsection