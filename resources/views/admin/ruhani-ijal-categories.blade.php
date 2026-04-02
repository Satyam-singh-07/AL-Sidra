@extends('admin.layouts.app')

@section('title', 'Ruhani Ijal Categories')

@section('content')

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        .action-btns .btn {
            margin-right: 5px;
        }

        .action-btns .btn:last-child {
            margin-right: 0;
        }

        .cat-content {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        /* Fix modal scrolling when summernote is open */
        .modal {
            overflow-y: auto !important;
        }
        
        .note-editable {
            background: #fff !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">Ruhani Ijal Categories Management</h2>

        <div class="d-flex gap-2">
            <a href="{{ route('ruhani-ijal.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i> Add Category
            </button>
        </div>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Name</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    <div class="cat-content">
                                        {!! $category->description !!}
                                    </div>
                                </td>

                                <td class="action-btns">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $category->id }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal{{ $category->id }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('ruhani-ijal-categories.update', $category->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-body">

                                                {{-- Name --}}
                                                <div class="mb-3">
                                                    <label class="form-label">Category Name *</label>
                                                    <input type="text" name="name" value="{{ $category->name }}"
                                                        class="form-control" required>
                                                </div>

                                                {{-- Content --}}
                                                <div class="mb-3">
                                                    <label class="form-label">Content</label>
                                                    <textarea name="description" class="form-control summernote">{{ $category->description }}</textarea>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>

                                                <button type="submit" class="btn btn-success">Update</button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Delete Modal --}}
                            <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST"
                                            action="{{ route('ruhani-ijal-categories.destroy', $category->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <div class="modal-body">
                                                <p>
                                                    Delete <strong>{{ $category->name }}</strong>?
                                                    This cannot be undone.
                                                </p>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No categories found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form method="POST" action="{{ route('ruhani-ijal-categories.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="description" class="form-control summernote" placeholder="Enter content here..."></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                Save Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JQuery (Required for Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // Re-initialize summernote when modal is opened to fix width issues
            $('.modal').on('shown.bs.modal', function () {
                $(this).find('.summernote').summernote({
                    focus: true
                });
            });
        });
    </script>

@endsection
