@extends('admin.layouts.app')

@section('title', 'Quote Logs')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success fw-bold">Quote Notification Logs</h2>
            <a href="{{ route('daily-quotes.index') }}" class="btn btn-outline-success">
                <i class="fas fa-list me-2"></i> Back to Quotes
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Quote</th>
                                <th>Sent To</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->quote ? Str::limit($log->quote->quote, 70) : 'Deleted Quote' }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ $log->sent_to_count }} Users
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->sent_at)->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No notification logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
