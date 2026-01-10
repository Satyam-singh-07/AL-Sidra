@extends('admin.layouts.app')

@section('title', 'Member Approval')

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
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        .status-approved {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .action-buttons .btn:last-child {
            margin-right: 0;
        }
        .modal-img-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .document-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .export-btn {
            border-radius: 8px;
        }
        .tab-content {
            padding-top: 1rem;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #198754;
            border-bottom: 3px solid #198754;
            background-color: transparent;
        }
    </style>


                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-success fw-bold mb-1">Member Approval</h2>
                        <p class="text-muted mb-0">Review and approve member applications</p>
                    </div>
                    {{-- <div>
                        <button class="btn btn-outline-success export-btn" id="exportBtn">
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
                                        <h6 class="text-muted mb-2">Pending Approval</h6>
                                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-clock text-primary fa-lg"></i>
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
                                        <h6 class="text-muted mb-2">Approved</h6>
                                        <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Rejected</h6>
                                        <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                                    </div>
                                    <div class="bg-danger bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
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
                                        <h6 class="text-muted mb-2">Total Members</h6>
                                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                                        <i class="fas fa-users text-info fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <ul class="nav nav-tabs mb-4" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}"
                        href="{{ route('members.index', ['tab' => 'pending']) }}">
                            <i class="fas fa-clock me-2"></i>
                            Pending
                            <span class="badge bg-warning">{{ $stats['pending'] }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'approved' ? 'active' : '' }}"
                        href="{{ route('members.index', ['tab' => 'approved']) }}">
                            <i class="fas fa-check-circle me-2"></i>
                            Approved
                            <span class="badge bg-success">{{ $stats['approved'] }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'rejected' ? 'active' : '' }}"
                        href="{{ route('members.index', ['tab' => 'rejected']) }}">
                            <i class="fas fa-times-circle me-2"></i>
                            Rejected
                            <span class="badge bg-danger">{{ $stats['rejected'] }}</span>
                        </a>
                    </li>

                </ul>


                <!-- Search and Filters -->
                <form method="GET" action="{{ route('members.index') }}">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3">

                                <!-- Search -->
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input
                                            type="text"
                                            name="search"
                                            class="form-control border-start-0"
                                            placeholder="Search by name or phone..."
                                            value="{{ request('search') }}"
                                        >
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="col-md-3">
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date -->
                                <div class="col-md-3">
                                    <input
                                        type="date"
                                        name="date"
                                        class="form-control"
                                        value="{{ request('date') }}"
                                    >
                                </div>

                                <!-- Actions -->
                                <div class="col-md-2 d-flex gap-2">
                                    <button class="btn btn-success w-100" type="submit">
                                        Filter
                                    </button>

                                    <a href="{{ route('members.index', ['tab' => $activeTab]) }}"
                                    class="btn btn-outline-secondary w-100">
                                        Clear
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>


                <!-- Tab Content -->
                <div class="tab-content" id="approvalTabsContent">
                    <!-- Pending Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <div class="card shadow-sm border-0">
                           @if ($activeTab === 'pending')
                                @include('admin.partials.pending-table')
                            @endif
                        </div>
                    </div>

                    <!-- Approved Tab -->
                    <div class="tab-pane fade show active" id="approved" role="tabpanel">
                        <div class="card shadow-sm border-0">
                            @if ($activeTab === 'approved')
                                @include('admin.partials.approved-table')
                            @endif
                        </div>
                    </div>

                    <!-- Rejected Tab -->
                    <div class="tab-pane fade show active" id="rejected" role="tabpanel">
                        <div class="card shadow-sm border-0">
                            @if ($activeTab === 'rejected')
                                @include('admin.partials.rejected-table')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <!-- View KYC Modal -->
    <div class="modal fade" id="viewKycModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2"></i>KYC Documents
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">

                    <!-- Member Header -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="user-avatar me-3" id="kycAvatar">--</div>
                        <div>
                            <h6 class="mb-1" id="kycName">Member Name</h6>
                            <small class="text-muted" id="kycMeta">Member ID</small>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="document-section">
                        <h6><i class="fas fa-graduation-cap me-2"></i>Educational Qualification</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Institute Name</label>
                                <p id="kycInstitute">—</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Degree Completion Year</label>
                                <p id="kycDegreeYear">—</p>
                            </div>
                        </div>

                        <label class="form-label text-muted">Degree Certificate</label>
                        <img id="degreeImage" class="modal-img-preview w-100">
                    </div>

                    <!-- Aadhaar -->
                    <div class="document-section">
                        <h6><i class="fas fa-id-card me-2"></i>Aadhaar Card</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Front Side</label>
                                <img id="aadhaarFront" class="modal-img-preview w-100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Back Side</label>
                                <img id="aadhaarBack" class="modal-img-preview w-100">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="document-section">
                        <h6><i class="fas fa-info-circle me-2"></i>Additional Information</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Member Category</label>
                                <p>
                                    <span class="badge bg-info bg-opacity-10 text-info" id="kycCategory">
                                        —
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Associated Place</label>
                                <p id="kycPlace">—</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                    <button class="btn btn-success" id="approveFromModal">
                        <i class="fas fa-check me-2"></i>Approve
                    </button>

                    <button class="btn btn-danger" id="rejectFromModal">
                        <i class="fas fa-times me-2"></i>Reject
                    </button>
                </div>

            </div>
        </div>
    </div>


    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Application
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this application:</p>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason</label>
                        <select class="form-select" id="rejectionReason">
                            <option value="">Select a reason</option>
                            <option value="incomplete_kyc">Incomplete KYC documents</option>
                            <option value="invalid_documents">Invalid or forged documents</option>
                            <option value="does_not_qualify">Does not meet qualification criteria</option>
                            <option value="duplicate_application">Duplicate application</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="additionalNotes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="additionalNotes" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The applicant will be notified about the rejection.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>Export Member Data
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Format</label>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="exportData('excel')">
                                <i class="fas fa-file-excel me-2"></i>Excel (.xlsx)
                            </button>
                            <button class="btn btn-outline-secondary" onclick="exportData('csv')">
                                <i class="fas fa-file-csv me-2"></i>CSV (.csv)
                            </button>
                            <button class="btn btn-outline-dark" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf me-2"></i>PDF (.pdf)
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Filter</label>
                        <select class="form-select" id="exportStatus">
                            <option value="all">All Status</option>
                            <option value="pending">Pending Only</option>
                            <option value="approved">Approved Only</option>
                            <option value="rejected">Rejected Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('viewKycModal')
    .addEventListener('show.bs.modal', function (event) {

    const button = event.relatedTarget;
    if (!button) return;

    const memberId = button.getAttribute('data-member-id');

    fetch(`{{ route('admin.members.kyc', ':id') }}`.replace(':id', memberId))
        .then(response => {
            if (!response.ok) throw new Error('Failed to load KYC');
            return response.json();
        })
        .then(({ member, kyc }) => {

            /* Header */
            document.getElementById('kycAvatar').innerText =
                member.name.substring(0, 2).toUpperCase();

            document.getElementById('kycName').innerText = member.name;

            document.getElementById('kycMeta').innerText =
                `Member ID: M${member.id} | Submitted: ${kyc?.submitted_at_human ?? '-'}`;

            /* Education */
            document.getElementById('kycInstitute').innerText =
                kyc?.institute_name ?? '-';

            document.getElementById('kycDegreeYear').innerText =
                kyc?.degree_complete_year ?? '-';

            document.getElementById('degreeImage').src =
                kyc?.degree_photo_url ?? '';

            /* Aadhaar */
            document.getElementById('aadhaarFront').src =
                kyc?.aadhaar_front_url ?? '';

            document.getElementById('aadhaarBack').src =
                kyc?.aadhaar_back_url ?? '';

            /* Category & Place */
            document.getElementById('kycCategory').innerText =
                member.member_profile?.category?.name ?? '-';

            document.getElementById('kycPlace').innerText =
                member.member_profile?.place?.name ?? '-';

            /* Action buttons */
            document.getElementById('approveFromModal').onclick =
                () => approveMember(member.id);

            document.getElementById('rejectFromModal').onclick =
                () => rejectMember(member.id);
        })
        .catch(error => {
            console.error(error);
            alert('Unable to load KYC details.');
        });
});


function approveMember(memberId) {
    if (!confirm('Approve this KYC?')) return;

    fetch(`{{ route('admin.members.kyc.approve', ':id') }}`.replace(':id', memberId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => {
        if (!res.ok) throw new Error('Approval failed');
        return res.json();
    })
    .then(() => {
        location.reload(); // simple & reliable
    })
    .catch(err => alert(err.message));
}

let rejectMemberId = null;

function rejectMember(memberId) {
    rejectMemberId = memberId;
    new bootstrap.Modal(document.getElementById('rejectionModal')).show();
}

document.getElementById('confirmReject').addEventListener('click', function () {

    const reason = document.getElementById('rejectionReason').value;
    const notes  = document.getElementById('additionalNotes').value;

    if (!reason) {
        alert('Please select a rejection reason');
        return;
    }

    fetch(`{{ route('admin.members.kyc.reject', ':id') }}`.replace(':id', rejectMemberId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            reason: reason,
            notes: notes,
        }),
    })
    .then(res => {
        if (!res.ok) throw new Error('Rejection failed');
        return res.json();
    })
    .then(() => {
        location.reload();
    })
    .catch(err => alert(err.message));
});


</script>
@endsection



