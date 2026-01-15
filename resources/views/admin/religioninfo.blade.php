@extends('admin.layouts.app')

@section('title', 'Religion & Islamic Information')

@section('content')

    <style>
        /* Specific styles for Religion Info */
        .info-card {
            border-left: 4px solid #198754;
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-title {
            color: #198754;
            font-weight: 600;
        }
        
        .info-content {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .info-category {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .info-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .info-card:hover .info-actions {
            opacity: 1;
        }
        
        .full-content {
            max-height: none;
            -webkit-line-clamp: unset;
        }
        
        .editor-toolbar {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            padding: 8px;
            border-radius: 4px 4px 0 0;
        }
        
        .editor-area {
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .preview-area {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            min-height: 100px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .preview-area img {
            max-width: 100%;
            height: auto;
        }
    </style>

     <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-success fw-bold">
                        <i class="fas fa-mosque me-2"></i>Religion & Islamic Information
                    </h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addInfoModal">
                        <i class="fas fa-plus me-2"></i> Add New Information
                    </button>
                </div>

                <!-- Category Filter -->
                <form method="GET" action="{{ route('religion-info.index') }}">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">

                                        <!-- Category -->
                                        <div class="col-md-3">
                                            <label class="form-label">Category</label>
                                            <select name="category" class="form-select" id="categoryFilter">
                                                <option value="all">All Categories</option>
                                                <option value="basic-beliefs" {{ request('category')=='basic-beliefs' ? 'selected' : '' }}>Basic Beliefs</option>
                                                <option value="prayer" {{ request('category')=='prayer' ? 'selected' : '' }}>Prayer (Salah)</option>
                                                <option value="fasting" {{ request('category')=='fasting' ? 'selected' : '' }}>Fasting (Sawm)</option>
                                                <option value="charity" {{ request('category')=='charity' ? 'selected' : '' }}>Charity (Zakat)</option>
                                                <option value="pilgrimage" {{ request('category')=='pilgrimage' ? 'selected' : '' }}>Pilgrimage (Hajj)</option>
                                                <option value="quran" {{ request('category')=='quran' ? 'selected' : '' }}>Quranic Studies</option>
                                                <option value="hadith" {{ request('category')=='hadith' ? 'selected' : '' }}>Hadith Studies</option>
                                                <option value="history" {{ request('category')=='history' ? 'selected' : '' }}>Islamic History</option>
                                                <option value="ethics" {{ request('category')=='ethics' ? 'selected' : '' }}>Islamic Ethics</option>
                                            </select>
                                        </div>

                                        <!-- Status -->
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" id="statusFilter">
                                                <option value="all">All Status</option>
                                                <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>

                                        <!-- Search + Buttons -->
                                        <div class="col-md-6">
                                            <label class="form-label">Search</label>

                                            <!-- DO NOT REMOVE flex-nowrap -->
                                            <div class="input-group flex-nowrap">
                                                <input
                                                    type="text"
                                                    name="search"
                                                    value="{{ request('search') }}"
                                                    class="form-control"
                                                    placeholder="Search by title or content..."
                                                >

                                                <button
                                                    class="btn btn-outline-success"
                                                    type="submit"
                                                >
                                                    <i class="fas fa-search"></i>
                                                </button>

                                                <a
                                                    href="{{ route('religion-info.index') }}"
                                                    class="btn btn-outline-secondary"
                                                >
                                                    <i class="fas fa-redo"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row g-4" id="infoCardsContainer">
                    @forelse($infos as $info)
                        <div class="col-lg-4 col-md-6"
                            data-category="{{ $info->category }}"
                            data-status="{{ $info->status }}">
                            <div class="card info-card shadow-sm h-100">
                                <div class="card-body position-relative">
                                    <span class="badge bg-success info-category">
                                        {{ ucfirst(str_replace('-', ' ', $info->category)) }}
                                    </span>

                                    <h5 class="card-title info-title mb-3">
                                        {{ $info->title }}
                                    </h5>

                                    <div class="info-content mb-3">
                                        {!! $info->content !!}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $info->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($info->status) }}
                                        </span>
                                        <small class="text-muted">
                                            Updated: {{ $info->updated_at->format('Y-m-d') }}
                                        </small>
                                    </div>

                                    <div class="info-actions mt-3">
                                        <button class="btn btn-sm btn-outline-primary btn-view"
                                                data-id="{{ $info->id }}">
                                            View
                                        </button>

                                        <button class="btn btn-sm btn-outline-warning btn-edit"
                                                data-id="{{ $info->id }}">
                                            Edit
                                        </button>

                                        <form action="{{ route('religion-info.destroy', $info->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this information?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <h5 class="text-muted">No information found</h5>
                        </div>
                    @endforelse
                </div>
                            @include('admin.partials.pagination', ['paginator' => $infos])


    <!-- Add Information Modal -->
    <div class="modal fade" id="addInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">
                        <i class="fas fa-plus-circle me-2"></i>Add New Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST"
                        action="{{ route('religion-info.store') }}"
                        enctype="multipart/form-data">
                    @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter information title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="basic-beliefs">Basic Beliefs</option>
                                    <option value="prayer">Prayer (Salah)</option>
                                    <option value="fasting">Fasting (Sawm)</option>
                                    <option value="charity">Charity (Zakat)</option>
                                    <option value="pilgrimage">Pilgrimage (Hajj)</option>
                                    <option value="quran">Quranic Studies</option>
                                    <option value="hadith">Hadith Studies</option>
                                    <option value="history">Islamic History</option>
                                    <option value="ethics">Islamic Ethics</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
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
                            <div class="form-control editor-area" contenteditable="true" placeholder="Enter detailed information here..."></div>
                            <input type="hidden" name="content" id="contentInput">
                        </div>
                        
                        {{-- <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div class="preview-area" id="contentPreview">
                                Preview will appear here...
                            </div>
                        </div> --}}
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Featured Image (Optional)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <div class="form-text">Max size: 2MB. Formats: JPG, PNG, GIF</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Information</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Information Modal -->
    <div class="modal fade" id="viewInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary">
                        <i class="fas fa-eye me-2"></i>View Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge bg-success" id="viewCategory"></span>
                        <span class="badge bg-secondary ms-2" id="viewStatus"></span>
                    </div>

                    <h4 id="viewTitle"></h4>

                    <div class="preview-area" id="viewContent"></div>

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <span id="viewUpdated"></span>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editInfoModal" data-bs-dismiss="modal">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Information Modal -->
    <div class="modal fade" id="editInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="fas fa-edit me-2"></i>Edit Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="id" id="editId">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" id="editTitle" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" id="editCategory" class="form-select" required>
                                    <option value="basic-beliefs">Basic Beliefs</option>
                                    <option value="prayer">Prayer</option>
                                    <option value="fasting">Fasting</option>
                                    <option value="charity">Charity</option>
                                    <option value="pilgrimage">Pilgrimage</option>
                                    <option value="quran">Quran</option>
                                    <option value="hadith">Hadith</option>
                                    <option value="history">History</option>
                                    <option value="ethics">Ethics</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <div class="form-control editor-area"
                                id="editEditor"
                                contenteditable="true"></div>
                            <input type="hidden" name="content" id="editContent">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Update Information</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Information Modal -->
    <div class="modal fade" id="deleteInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete this information? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <strong>Title:</strong> The Five Pillars of Islam<br>
                        <strong>Category:</strong> Basic Beliefs<br>
                        <strong>Status:</strong> Active<br>
                        <strong>Last Updated:</strong> 2025-03-01
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete Information</button>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('religion-info.store') }}"]');
    const editor = document.querySelector('.editor-area');
    const contentInput = document.getElementById('contentInput');

    form.addEventListener('submit', function () {
        contentInput.value = editor.innerHTML.trim();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ================= VIEW ================= */
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/admin/religion-info/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('viewTitle').innerText = data.title;
                    document.getElementById('viewContent').innerHTML = data.content;
                    document.getElementById('viewCategory').innerText = data.category;
                    document.getElementById('viewStatus').innerText = data.status;
                    document.getElementById('viewUpdated').innerText =
                        `Last updated: ${data.updated_at}`;

                    new bootstrap.Modal(
                        document.getElementById('viewInfoModal')
                    ).show();
                });
        });
    });

    /* ================= EDIT ================= */
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/admin/religion-info/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editTitle').value = data.title;
                    document.getElementById('editCategory').value = data.category;
                    document.getElementById('editEditor').innerHTML = data.content;
                    document.getElementById('editStatus').value = data.status;

                    new bootstrap.Modal(
                        document.getElementById('editInfoModal')
                    ).show();
                });
        });
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/admin/religion-info/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editTitle').value = data.title;
                    document.getElementById('editCategory').value = data.category;
                    document.getElementById('editEditor').innerHTML = data.content;
                    document.getElementById('editStatus').value = data.status;

                    const form = document.getElementById('editForm');
                    form.action = `/admin/religion-info/${data.id}`;

                    new bootstrap.Modal(
                        document.getElementById('editInfoModal')
                    ).show();
                });
        });
    });

    // sync contenteditable before submit
    document.getElementById('editForm').addEventListener('submit', function () {
        document.getElementById('editContent').value =
            document.getElementById('editEditor').innerHTML.trim();
    });

});
</script>

@endsection