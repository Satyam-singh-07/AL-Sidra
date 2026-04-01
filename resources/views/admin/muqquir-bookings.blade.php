@extends('admin.layouts.app')

@section('title', 'Muqquir Bookings')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Muqquir Bookings</h2>
            <p class="text-muted mb-0">View and track all booking requests between members and muqquirs</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Sr No</th>
                            <th>Member</th>
                            <th>Muqquir</th>
                            <th>Booking Date</th>
                            <th>Travel Fee</th>
                            <th>Status</th>
                            <th>Requested On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $booking->user->name }}</div>
                                    <small class="text-muted">{{ $booking->user->phone }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $booking->muqquirProfile->user->name }}</div>
                                    <small class="text-muted">{{ $booking->muqquirProfile->user->phone }}</small>
                                </td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>
                                    @if($booking->travel_fee)
                                        <span class="badge bg-primary fs-6">₹ {{ number_format($booking->travel_fee, 2) }}</span>
                                    @else
                                        <span class="text-muted">Not Proposed</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($booking->status == 'accepted') bg-success 
                                        @elseif($booking->status == 'pending') bg-warning text-dark 
                                        @elseif($booking->status == 'fee_proposed') bg-info text-white
                                        @else bg-danger @endif">
                                        {{ str_replace('_', ' ', ucfirst($booking->status)) }}
                                    </span>
                                </td>
                                <td>{{ $booking->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No bookings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
