@extends('admin.layouts.app')

@section('title', 'Roles Management')

@section('content')
    <style>
        .permission-box {
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 6px;
            background: #fff;
        }

        .action-buttons .btn {
            margin-right: 6px;
        }
    </style>

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Roles Management</h4>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus me-1"></i> Add Role
        </button>
    </div>

    {{-- ROLES TABLE --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>All Roles</span>
            <span class="badge bg-primary">{{ $roles->count() }}</span>
        </div>

        <div class="card-body p-0">
            @if ($roles->isEmpty())
                <div class="text-center py-5 text-muted">
                    No roles found
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Modules</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($roles as $index => $role)
                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                    </td>

                                    <td>
                                        @forelse($role->modules as $module)
                                            <span class="badge bg-secondary me-1 mb-1">
                                                {{ ucfirst(str_replace('_', ' ', $module->module)) }}
                                            </span>
                                        @empty
                                            —
                                        @endforelse
                                    </td>

                                    <td>{{ $role->created_at->format('d M Y') }}</td>

                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editRoleModal" data-id="{{ $role->id }}"
                                            data-name="{{ $role->name }}" data-modules='@json($role->modules->pluck('module'))'>
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @if ($role->slug !== 'admin')
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteRoleModal" data-id="{{ $role->id }}"
                                                data-name="{{ $role->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ================= ADD ROLE MODAL ================= --}}
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('roles.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Role</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modules</label>
                        <div class="permission-box">
                            @foreach ($modules as $module)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="modules[]"
                                        value="{{ $module }}">
                                    <label class="form-check-label">
                                        {{ ucfirst(str_replace('_', ' ', $module)) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= EDIT ROLE MODAL ================= --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="editRoleForm" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name *</label>
                        <input type="text" id="editRoleName" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Modules</label>
                        <div class="permission-box">
                            @foreach ($modules as $module)
                                <div class="form-check">
                                    <input class="form-check-input edit-module" type="checkbox" name="modules[]"
                                        value="{{ $module }}">
                                    <label class="form-check-label">
                                        {{ ucfirst(str_replace('_', ' ', $module)) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= DELETE ROLE MODAL ================= --}}
    <div class="modal fade" id="deleteRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="deleteRoleForm" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Delete Role</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete
                    <strong id="deleteRoleName"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= JS ================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const editModal = document.getElementById('editRoleModal');

            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                const id = button.dataset.id;
                const name = button.dataset.name;
                const modules = JSON.parse(button.dataset.modules);

                document.getElementById('editRoleName').value = name;
                document.getElementById('editRoleForm').action = `/admin/roles/${id}`;

                document.querySelectorAll('.edit-module').forEach(cb => {
                    cb.checked = modules.includes(cb.value);
                });
            });

            const deleteModal = document.getElementById('deleteRoleModal');

            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                document.getElementById('deleteRoleName').textContent = button.dataset.name;
                document.getElementById('deleteRoleForm').action = `/admin/roles/${button.dataset.id}`;
            });

        });
    </script>
@endsection
