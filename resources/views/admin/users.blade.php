@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')

    <style>
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #198754 0%, #0d6efd 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .phone-number {
            color: #666;
            font-family: monospace;
        }

        .registration-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .stats-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-top: none;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(25, 135, 84, 0.05);
        }

        .export-btn {
            border-radius: 8px;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Users</h2>
            <p class="text-muted mb-0">Registered users in the system</p>
        </div>
        {{-- <div>
                        <button class="btn btn-outline-success export-btn">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                    </div> --}}
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Today</h6>
                            <h3 class="mb-0">{{ $stats['today'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-user-check text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">This Month</h6>
                            <h3 class="mb-0">{{ $stats['month'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-calendar-alt text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">This Year</h6>
                            <h3 class="mb-0">{{ $stats['year'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-chart-line text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control border-start-0" placeholder="Search by name or phone...">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select name="sort" class="form-select">
                            <option value="">Sort by Date</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                                Newest First
                            </option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                                Oldest First
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-secondary w-100" type="submit">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>

                        <a href="{{ route('users.index') }}" class="btn btn-outline-danger w-100">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Users Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Location</th>
                            <th>Registration Date</th>
                            <th>Active Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="user-name">{{ $user->name }}</div>
                                            {{-- <small class="text-muted">User ID: {{ $user->id }}</small> --}}
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="phone-number">{{ $user->phone }}</div>
                                </td>

                                <td>
                                    {{ $user->address ?? '-' }}
                                </td>

                                <td>
                                    {{ $user->latitude }}, {{ $user->longitude }}
                                    <br>
                                    <a href="https://www.google.com/maps?q={{ $user->latitude }},{{ $user->longitude }}"
                                        target="_blank" class="text-primary">
                                        View on Map
                                    </a>
                                </td>

                                <td>
                                    <div class="registration-date">
                                        {{ $user->created_at->format('Y-m-d') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $user->created_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <form action="{{ route('users.toggle-status', $user->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf

                                        <button type="submit"
                                            class="btn btn-sm {{ $user->status === 'active' ? 'btn-success' : 'btn-secondary' }}">
                                            {{ ucfirst($user->status) }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $users])
            </div>

            <!-- Summary -->
            <!-- <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="alert alert-light border">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                                    <span>Showing 1 to 10 of 1,248 users</span>
                                                </div>
                                                <div class="text-muted">
                                                    Last updated: Today, 10:30 AM
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
        </div>
    </div>
@endsection
