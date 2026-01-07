@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<h2 class="text-success fw-bold mb-4">Dashboard Overview</h2>

<div class="row g-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex">
                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                    <i class="fas fa-book fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold"></h5>
                    <p class="text-muted mb-0">Quran PDFs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- repeat for others -->

</div>

@endsection
