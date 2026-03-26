@extends('admin.layouts.app')

@section('title', 'Ruhani Ijal Categories')

@section('content')

    <style>
        .action-btns .btn {
            margin-right: 5px;
        }

        .action-btns .btn:last-child {
            margin-right: 0;
        }

        .editor-toolbar {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            padding: 8px;
            border-radius: 4px 4px 0 0;
        }

        .editor-area {
            min-height: 150px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .cat-content {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
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

                                        <form method="POST" action="{{ route('ruhani-ijal-categories.update', $category->id) }}" class="edit-category-form">
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
                                                    <div class="editor-toolbar">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold">
                                                            <i class="fas fa-bold"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="italic">
                                                            <i class="fas fa-italic"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList">
                                                            <i class="fas fa-list-ul"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList">
                                                            <i class="fas fa-list-ol"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-control editor-area" contenteditable="true">{!! $category->description !!}</div>
                                                    <input type="hidden" name="description" class="content-input">
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
                    <form method="POST" action="{{ route('ruhani-ijal-categories.store') }}" id="addCategoryForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <div class="editor-toolbar">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-command="italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                            </div>
                            <div class="form-control editor-area" contenteditable="true" placeholder="Enter content here..."></div>
                            <input type="hidden" name="description" id="addContentInput">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Toolbar Commands
            document.querySelectorAll('[data-command]').forEach(button => {
                button.addEventListener('click', function() {
                    const command = this.dataset.command;
                    document.execCommand(command, false, null);
                });
            });

            // Handle Add Form Submission
            const addForm = document.getElementById('addCategoryForm');
            if (addForm) {
                addForm.addEventListener('submit', function() {
                    const editor = this.querySelector('.editor-area');
                    const contentInput = document.getElementById('addContentInput');
                    contentInput.value = editor.innerHTML.trim();
                });
            }

            // Handle Edit Forms Submission
            document.querySelectorAll('.edit-category-form').forEach(form => {
                form.addEventListener('submit', function() {
                    const editor = this.querySelector('.editor-area');
                    const contentInput = this.querySelector('.content-input');
                    contentInput.value = editor.innerHTML.trim();
                });
            });
        });
    </script>

@endsection
