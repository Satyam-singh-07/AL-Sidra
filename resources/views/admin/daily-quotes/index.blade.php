@extends('admin.layouts.app')

@section('title', 'Daily Quotes')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success fw-bold">Daily Quotes Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addQuoteModal">
                <i class="fas fa-plus me-2"></i> Add Daily Quote
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Quote</th>
                                <th>Scheduled Time (IST)</th>
                                <th>Status</th>
                                <th>Last Sent At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td>{{ $quote->id }}</td>
                                    <td>{{ Str::limit($quote->quote, 50) }}</td>
                                    <td>{{ $quote->scheduled_time }}</td>
                                    <td>
                                        <span class="badge bg-{{ $quote->is_active ? 'success' : 'secondary' }}">
                                            {{ $quote->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $quote->last_sent_at ? \Carbon\Carbon::parse($quote->last_sent_at)->format('d M Y, h:i A') : 'Never' }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editQuote{{ $quote->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteQuote{{ $quote->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editQuote{{ $quote->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('daily-quotes.update', $quote) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Edit Quote #{{ $quote->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Quote Content</label>
                                                        <textarea name="quote" class="form-control" rows="3" required>{{ $quote->quote }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Scheduled Time (24h format HH:mm)</label>
                                                        <input type="time" name="scheduled_time" class="form-control" value="{{ $quote->scheduled_time }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="is_active" class="form-select">
                                                            <option value="1" @selected($quote->is_active)>Active</option>
                                                            <option value="0" @selected(!$quote->is_active)>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">Update Quote</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteQuote{{ $quote->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('daily-quotes.destroy', $quote) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5>Delete Quote #{{ $quote->id }}</h5>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this scheduled quote?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addQuoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Schedule New Daily Quote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('daily-quotes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Quote Content *</label>
                            <textarea name="quote" class="form-control" rows="3" placeholder="Enter the message to send..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Scheduled Time (24h format HH:mm) *</label>
                            <input type="time" name="scheduled_time" class="form-control" required>
                            <small class="text-muted">Notification will be sent daily at this time (IST).</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Schedule Quote</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
