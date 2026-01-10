@extends('admin.layouts.app')

@section('title', 'Member Categories')

@section('content')

    <style>
        .action-btns .btn { margin-right: 5px; }
        .action-btns .btn:last-child { margin-right: 0; }
        .status-active { background-color: #198754; color: white; }
        .status-inactive { background-color: #6c757d; color: white; }
    </style>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-success fw-bold">Member Categories</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-2"></i> Add Category
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($memberCategories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td>
                                            <form method="POST"
                                                action="{{ route('membercategories.toggle-status', $category->id) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                    class="btn btn-sm 
                                                    {{ $category->status === 'active' ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ ucfirst($category->status) }}
                                                </button>
                                            </form>
                                        </td>
                                            <td class="action-btns">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $category->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>

                                                <button class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $category->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                    </tr>
                                    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Category</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <form method="POST" action="{{ route('membercategories.update', $category->id) }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Category Name *</label>
                                                            <input type="text" name="name"
                                                                value="{{ $category->name }}"
                                                                class="form-control" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control">{{ $category->description }}</textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active" {{ $category->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ $category->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <form method="POST" action="{{ route('membercategories.destroy', $category->id) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <div class="modal-body">
                                                        <p>Delete <strong>{{ $category->name }}</strong>? This cannot be undone.</p>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No categories found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Category Modal -->
                <div class="modal fade" id="addModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-success">Add New Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('membercategories.store') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Category Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Save Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
@endsection