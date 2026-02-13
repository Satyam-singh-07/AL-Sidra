@extends('admin.layouts.app')

@section('title', 'Users & Permissions')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Users Management</h4>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i> Add User
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            All Users
        </div>

        <div class="card-body p-0">
            @if ($users->isEmpty())
                <div class="text-center py-5 text-muted">
                    No users found
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>

                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-secondary">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{-- Edit --}}
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editUserModal" data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}" data-email="{{ $user->email }}"
                                            data-role="{{ $user->roles->pluck('id')->first() }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Delete --}}
                                        @if (!$user->roles->pluck('slug')->contains('super_admin'))
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal" data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        {{-- Toggle Status --}}
                                        @if (!$user->roles->pluck('slug')->contains('super_admin'))
                                            <form action="{{ route('permissions.toggleStatus', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                    class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                    @if ($user->status === 'active')
                                                        <i class="fas fa-ban"></i>
                                                    @else
                                                        <i class="fas fa-check"></i>
                                                    @endif
                                                </button>
                                            </form>
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

    {{-- ================= ADD USER MODAL ================= --}}
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('permissions.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Create User</button>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= EDIT USER MODAL ================= --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="editUserForm" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" id="editUserName" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" id="editUserEmail" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" id="editUserRole" class="form-select" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Update User</button>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= DELETE USER MODAL ================= --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="deleteUserForm" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete
                    <strong id="deleteUserName"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const editModal = document.getElementById('editUserModal');

            editModal.addEventListener('show.bs.modal', function(event) {

                const button = event.relatedTarget;

                const id = button.dataset.id;
                const name = button.dataset.name;
                const email = button.dataset.email;
                const role = button.dataset.role;

                document.getElementById('editUserName').value = name;
                document.getElementById('editUserEmail').value = email;
                document.getElementById('editUserRole').value = role;

                document.getElementById('editUserForm').action = `/admin/permissions/${id}`;
            });

        });

        const deleteModal = document.getElementById('deleteUserModal');

        deleteModal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            const id = button.dataset.id;
            const name = button.dataset.name;

            document.getElementById('deleteUserName').textContent = name;

            document.getElementById('deleteUserForm').action =
                `/admin/permissions/${id}`;
        });
    </script>

@endsection
