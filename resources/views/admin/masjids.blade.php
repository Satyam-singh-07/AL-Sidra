@extends('admin.layouts.app')

@section('title', 'Masjid Management')

@section('content')

    <style>
        .masjid-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .action-buttons .btn {
            margin-right: 5px;
        }

        .action-buttons .btn:last-child {
            margin-right: 0;
        }

        .search-box {
            max-width: 300px;
        }

        .location-info {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Masjid Listings</h2>
            <p class="text-muted mb-0">Manage all Islamic centers, mosques, and madarsas</p>
        </div>
        <a href="{{ route('masjids.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i> Add New Masjid
        </a>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0"
                            placeholder="Search by name, location, or community...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Communities</option>
                        <option value="sunni">Sunni</option>
                        <option value="shia">Shia</option>
                        <option value="ahmadiyya">Ahmadiyya</option>
                        <option value="sufi">Sufi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending Verification</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Masjids</h6>
                            <h3 class="mb-0">24</h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-mosque text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active</h6>
                            <h3 class="mb-0">18</h3>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending</h6>
                            <h3 class="mb-0">4</h3>
                        </div>
                        <div class="bg-warning bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Capacity</h6>
                            <h3 class="mb-0">5,250</h3>
                        </div>
                        <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                            <i class="fas fa-users text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Masjids Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Masjid Details</th>
                            <th>Location</th>
                            <th>Community</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Data Row 1 -->
                        <tr>
                            <td class="fw-bold">#001</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <img src="https://via.placeholder.com/60" alt="Masjid" class="masjid-img me-3"> -->
                                    <div>
                                        <h6 class="mb-1">Jamia Masjid Al-Noor</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>Sector 62, Noida
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <div><i class="fas fa-map-pin me-1"></i>Lat: 28.6274, Long: 77.3760</div>
                                    <small>Near Golf Course Metro Station</small>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                    Sunni
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success status-badge">
                                    <i class="fas fa-check-circle me-1"></i>Verified
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="masjid-edit.html?id=001" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Sample Data Row 2 -->
                        <tr>
                            <td class="fw-bold">#002</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <img src="https://via.placeholder.com/60/FF5722/ffffff?text=Madarsa" alt="Madarsa" class="masjid-img me-3"> -->
                                    <div>
                                        <h6 class="mb-1">Madarsa Islamia</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>Haldwani, Uttarakhand
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <div><i class="fas fa-map-pin me-1"></i>Lat: 29.2183, Long: 79.5132</div>
                                    <small>Near Railway Station Road</small>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    Shia
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark status-badge">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="masjid-edit.html?id=002" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </td>
                        </tr>

                        <!-- Sample Data Row 3 -->
                        <tr>
                            <td class="fw-bold">#003</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <img src="https://via.placeholder.com/60/4CAF50/ffffff?text=Masjid" alt="Masjid" class="masjid-img me-3"> -->
                                    <div>
                                        <h6 class="mb-1">Masjid Al-Falah</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>Ghaziabad, UP
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <div><i class="fas fa-map-pin me-1"></i>Lat: 28.6692, Long: 77.4538</div>
                                    <small>Vasundhara Sector 5</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                    Sufi
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success status-badge">
                                    <i class="fas fa-check-circle me-1"></i>Verified
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="masjid-edit.html?id=003" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Sample Data Row 4 -->
                        <tr>
                            <td class="fw-bold">#004</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- <img src="https://via.placeholder.com/60/9C27B0/ffffff?text=Center" alt="Center" class="masjid-img me-3"> -->
                                    <div>
                                        <h6 class="mb-1">Islamic Center Qadria</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>Delhi
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="location-info">
                                    <div><i class="fas fa-map-pin me-1"></i>Lat: 28.7041, Long: 77.1025</div>
                                    <small>Jamia Nagar, Okhla</small>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    Ahmadiyya
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger status-badge">
                                    <i class="fas fa-times-circle me-1"></i>Inactive
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="masjid-edit.html?id=004" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#viewModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">
                        <i class="fas fa-mosque me-2"></i>Masjid Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="https://via.placeholder.com/200" alt="Masjid" class="img-fluid rounded">
                            <h5 class="mt-3">Jamia Masjid Al-Noor</h5>
                            <span class="badge bg-success">Verified</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Masjid ID</label>
                                    <p class="fw-bold">#001</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Community</label>
                                    <p><span class="badge bg-success">Sunni</span></p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Address</label>
                                    <p>Sector 62, Noida, Uttar Pradesh - 201301</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Location</label>
                                    <p>Lat: 28.6274, Long: 77.3760</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label text-muted">Registration Date</label>
                                    <p>2024-12-15</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Donation Passbook</label>
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <p class="mb-1">donation_passbook_001.pdf</p>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="masjid-edit.html?id=001" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this masjid? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <strong>Masjid:</strong> Jamia Masjid Al-Noor<br>
                        <strong>ID:</strong> #001<br>
                        <strong>Location:</strong> Sector 62, Noida
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete Masjid</button>
                </div>
            </div>
        </div>
    </div>

@endsection
