@extends('admin.layouts.app')

@section('title', 'Yateems Help Management')

@section('content')

    <style>
        .help-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .action-buttons .btn {
            margin-right: 5px;
        }

        .action-buttons .btn:last-child {
            margin-right: 0;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Yateems Help Listings</h2>
            <p class="text-muted mb-0">Manage all Yateems help requests and donation details</p>
        </div>
        <a href="{{ route('yateems-helps.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i> Add Yateems Help
        </a>
    </div>

    <!-- Search -->
    <form method="GET" class="row g-3 align-items-center mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                placeholder="Search by title">
        </div>

        <div class="col-md-2">
            <button class="btn btn-success w-100">Search</button>
        </div>

        <div class="col-md-2">
            <a href="{{ route('yateems-helps.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Details</th>
                            <th>Bank Info</th>
                            <th>UPI</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($yateemsHelps as $help)
                            <tr>
                                <td class="fw-bold">#{{ $loop->iteration }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($help->images->first())
                                            <img src="{{ asset('storage/' . $help->images->first()->image) }}"
                                                class="help-img me-3">
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $help->title }}</h6>
                                            <small class="text-muted">
                                                {{ Str::limit($help->description, 50) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="small">
                                        <div><strong>{{ $help->bank_name }}</strong></div>
                                        <div>IFSC: {{ $help->ifsc_code }}</div>
                                        <div>A/C: {{ Str::mask($help->account_no, '*', 0, strlen($help->account_no) - 4) }}
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $help->upi_id ?? '—' }}
                                </td>
                                <td>
                                    <span
                                        class="badge status-badge
            @if ($help->status === 'active') bg-success
            @elseif ($help->status === 'pending') bg-warning text-dark
            @else bg-secondary @endif"
                                        data-id="{{ $help->id }}" style="cursor:pointer;">
                                        {{ ucfirst($help->status) }}
                                    </span>
                                </td>


                                <td class="action-buttons">
                                    <a href="{{ route('yateems-helps.show', $help) }}"
                                        class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('yateems-helps.edit', $help) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('yateems-helps.destroy', $help) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No Yateems Help records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                @include('admin.partials.pagination', ['paginator' => $yateemsHelps])
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('status-badge')) return;

            const badge = e.target;
            const id = badge.dataset.id;

            fetch(`/admin/yateems-helps/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                    badge.classList.remove(
                        'bg-success',
                        'bg-warning',
                        'bg-secondary',
                        'text-dark'
                    );

                    if (data.status === 'active') {
                        badge.classList.add('bg-success');
                    } else if (data.status === 'pending') {
                        badge.classList.add('bg-warning', 'text-dark');
                    } else {
                        badge.classList.add('bg-secondary');
                    }
                });
        });
    </script>

@endsection
