@extends('admin.layouts.app')

@section('title', 'Jobs Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-success fw-bold">Jobs Management</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('job-categories.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-layer-group me-2"></i> Categories
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <p>This is the Jobs management module. You can add job listings and manage them here.</p>
    </div>
</div>

@endsection
