@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')

    <style>
        /* Specific styles for Banner images */
        .banner-table-img {
            width: 120px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .banner-preview-container {
            width: 100%;
            height: 150px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .banner-preview-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .table-actions .btn { margin-right: 5px; }
        .table-actions .btn:last-child { margin-right: 0; }
    </style>

            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-success fw-bold">Banner Management</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                        <i class="fas fa-plus me-2"></i> Add Banner
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Banner Image</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($banners as $banner)
                                <tr>
                                    <td>{{ $banner->id }}</td>
                                    <td>
                                        <img src="{{ $banner->image }}" class="banner-table-img">
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $banner->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($banner->status) }}
                                        </span>
                                    </td>
                                    <td class="table-actions">
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editBanner{{ $banner->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBanner{{ $banner->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

    <div class="modal fade" id="editBanner{{ $banner->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('banners.update', $banner) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Banner #{{ $banner->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <img src="{{ $banner->image }}" class="img-fluid mb-3">

                        <input type="file" name="image" class="form-control mb-3">

                        <select name="status" class="form-select">
                            <option value="active" @selected($banner->status === 'active')>Active</option>
                            <option value="inactive" @selected($banner->status === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


   <div class="modal fade" id="deleteBanner{{ $banner->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('banners.destroy', $banner) }}">
                @csrf
                @method('DELETE')

                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5>Delete Banner #{{ $banner->id }}</h5>
                    </div>

                    <div class="modal-body">
                        This action cannot be undone.
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
                                @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>

                                        <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Add New Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('banners.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Banner Image *</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <div class="form-text">Recommended size: 800x400px (Max 2MB)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Banner</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
@endsection