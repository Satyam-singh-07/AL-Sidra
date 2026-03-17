@extends('admin.layouts.app')

@section('title', 'Ruhani Ijal Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">Ruhani Ijal Management</h2>

        <div class="d-flex gap-2">
            <a href="{{ route('ruhani-ijal-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-2"></i> Ruhani Ijal Categories
            </a>

            <a href="{{ route('ruhani-ijal-aamils.index') }}" class="btn btn-outline-success">
                <i class="fas fa-users-cog me-2"></i> Aamil Registrations
            </a>

            <button class="btn btn-success">
                <i class="fas fa-plus me-2"></i> Add Ruhani Ijal
            </button>
        </div>
    </div>
...


    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                No Ruhani Ijal items found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
