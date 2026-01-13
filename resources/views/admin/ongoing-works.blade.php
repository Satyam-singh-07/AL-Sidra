@extends('admin.layouts.app')

@section('title', 'Ongoing Work')

@section('content')

    <style>
        .work-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .media-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-image, .preview-video {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            object-fit: cover;
        }
        .preview-video {
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .file-input-label {
            border: 2px dashed #dee2e6;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .file-input-label:hover {
            border-color: #198754;
            background: #f1f7f4;
        }
        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .file-list li {
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .address-truncate {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table-actions .btn {
            margin-right: 5px;
        }
        .table-actions .btn:last-child {
            margin-right: 0;
        }
        .upload-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .progress-container {
            display: none;
            margin-top: 10px;
        }
    </style>

 <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-success fw-bold">Ongoing Works</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addWorkModal">
                        <i class="fas fa-plus me-2"></i> Add New Work
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Images/Video</th>
                                        <th>Description</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($works as $work)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <strong>{{ $work->title }}</strong>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                @foreach($work->images->take(2) as $image)
                                                    <img src="{{ asset('storage/'.$image->path) }}" class="work-image">
                                                @endforeach

                                                @if($work->videos->count())
                                                    <div class="work-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-video text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <td>{{ Str::limit($work->description, 60) }}</td>

                                        <td class="address-truncate">{{ $work->address }}</td>

                                        <td>
                                            <span class="status-badge bg-primary">
                                                {{ ucfirst(str_replace('-', ' ', $work->status)) }}
                                            </span>
                                        </td>

                                        <td>{{ $work->created_at->format('Y-m-d') }}</td>

                                        <td class="table-actions">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#viewWorkModal"
                                                onclick="viewWork({{ $work->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <button class="btn btn-sm btn-outline-warning"
                                                onclick="editWork({{ $work->id }})"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editWorkModal">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="setDeleteId({{ $work->id }})"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteWorkModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No ongoing works found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                            @include('admin.partials.pagination', ['paginator' => $works])
                    </div>
                </div>


                <!-- Add Work Modal -->
    <div class="modal fade" id="addWorkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Add New Ongoing Work</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addWorkForm"
                        action="{{ route('ongoing-work.store') }}"
                        method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter work title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="planning">Planning</option>
                                    <option value="in-progress" selected>In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="on-hold">On Hold</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter detailed description of the work" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Enter complete address" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Images (Minimum 3) *</label>
                            <div class="file-input-label" onclick="document.getElementById('imageUpload').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Click to upload images</p>
                                <p class="upload-requirements">Minimum 5 images required | Max 10MB each | JPG, PNG, GIF</p>
                            </div>
                            <input type="file" id="imageUpload" name="images[]" class="d-none" multiple accept="image/*" onchange="handleImageUpload(this)">
                            <div class="media-preview-container" id="imagePreview"></div>
                            <div class="progress-container" id="imageProgress">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Videos (Minimum 2) *</label>
                            <div class="file-input-label" onclick="document.getElementById('videoUpload').click()">
                                <i class="fas fa-video fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Click to upload videos</p>
                                <p class="upload-requirements">Minimum 2 videos required | Max 50MB each | MP4, MOV, AVI</p>
                            </div>
                            <input type="file" id="videoUpload" name="videos[]" class="d-none" multiple accept="video/*" onchange="handleVideoUpload(this)">
                            <div class="media-preview-container" id="videoPreview"></div>
                            <div class="progress-container" id="videoProgress">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" rows="2" placeholder="Any additional information"></textarea>
                        </div> -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" onclick="saveWork()">Save Work</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Work Modal -->
    <div class="modal fade" id="viewWorkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary">View Work Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h4 id="viewTitle">Masjid Al-Noor Expansion</h4>
                            <p class="text-muted mb-2">ID: <span id="viewId">1</span></p>
                            <span class="badge bg-info" id="viewStatus">In Progress</span>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1"><strong>Date Added:</strong></p>
                            <p id="viewDate">2025-01-15</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p id="viewDescription" class="border rounded p-3 bg-light">Expanding prayer hall capacity from 500 to 1000 worshippers with new facilities including wudu area, library, and community hall.</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Address</h6>
                        <p id="viewAddress" class="border rounded p-3 bg-light">123 Islamic Center Road, Makkah District, Riyadh 12345, Saudi Arabia</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6>Images <span class="badge bg-secondary" id="imageCount">5</span></h6>
                        <div class="row g-2" id="viewImages">
                            <div class="col-3">
                                <img src="https://via.placeholder.com/150x150/198754/fff?text=Img1" class="img-fluid rounded" alt="Image">
                            </div>
                            <div class="col-3">
                                <img src="https://via.placeholder.com/150x150/0d6efd/fff?text=Img2" class="img-fluid rounded" alt="Image">
                            </div>
                            <!-- More images will be populated by JS -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Videos <span class="badge bg-secondary" id="videoCount">2</span></h6>
                        <div class="row g-2" id="viewVideos">
                            <div class="col-4">
                                <div class="border rounded p-3 text-center bg-light">
                                    <i class="fas fa-video fa-2x text-muted mb-2"></i>
                                    <p class="mb-0">Construction Progress</p>
                                </div>
                            </div>
                            <!-- More videos will be populated by JS -->
                        </div>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <h6>Additional Notes</h6>
                        <p id="viewNotes" class="border rounded p-3 bg-light">Work is progressing as scheduled. Expected completion by June 2025.</p>
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Work Modal -->
    <div class="modal fade" id="editWorkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">Edit Work</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWorkForm">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" id="editId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" id="editTitle" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" id="editStatus">
                                    <option value="planning">Planning</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="on-hold">On Hold</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="address" id="editAddress" rows="2" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Images</label>
                            <div class="media-preview-container" id="currentImages">
                                <!-- Current images will be shown here -->
                            </div>
                            <label class="form-label mt-3">Add More Images</label>
                            <input type="file" class="form-control" multiple accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Videos</label>
                            <div class="media-preview-container" id="currentVideos">
                                <!-- Current videos will be shown here -->
                            </div>
                            <label class="form-label mt-3">Add More Videos</label>
                            <input type="file" class="form-control" multiple accept="video/*">
                        </div>
                        
                        <!-- <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="editNotes" rows="2"></textarea>
                        </div> -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="updateWork()">Update Work</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Work Modal -->
    <div class="modal fade" id="deleteWorkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this work? All associated images and videos will be permanently removed.</p>
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Work Title:</strong> <span id="deleteTitle">Masjid Al-Noor Expansion</span><br>
                        <strong>ID:</strong> <span id="deleteId">1</span><br>
                        <strong>Status:</strong> <span id="deleteStatus" class="badge bg-info">In Progress</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="deleteWork()">Delete Work</button>
                </div>
            </div>
        </div>
    </div>

    <script>
let selectedImages = [];
let selectedVideos = [];
let deleteWorkId = null;


function handleImageUpload(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    selectedImages = Array.from(input.files);

    selectedImages.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'preview-image';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>
<script>
function handleVideoUpload(input) {
    const preview = document.getElementById('videoPreview');
    preview.innerHTML = '';
    selectedVideos = Array.from(input.files);

    selectedVideos.forEach(file => {
        const div = document.createElement('div');
        div.className = 'preview-video';
        div.innerHTML = `
            <i class="fas fa-video"></i>
        `;
        preview.appendChild(div);
    });
}
</script>
<script>
function saveWork() {
    if (selectedImages.length < 3) {
        alert('Minimum 3 images required');
        return;
    }

    if (selectedVideos.length < 2) {
        alert('Minimum 2 videos required');
        return;
    }

    document.getElementById('addWorkForm').submit();
}
</script>
<script>
function viewWork(id) {
    fetch(`/admin/ongoing-work/${id}`)
        .then(res => res.json())
        .then(work => {
            document.getElementById('viewTitle').innerText = work.title;
            document.getElementById('viewId').innerText = work.id;
            document.getElementById('viewStatus').innerText = work.status.replace('-', ' ');
            document.getElementById('viewDate').innerText = work.created_at.substring(0,10);
            document.getElementById('viewDescription').innerText = work.description;
            document.getElementById('viewAddress').innerText = work.address;

            // Images
            const imgWrap = document.getElementById('viewImages');
            imgWrap.innerHTML = '';
            work.images.forEach(img => {
                imgWrap.innerHTML += `
                    <div class="col-3">
                        <img src="${img.url}" class="img-fluid rounded">
                    </div>
                `;
            });

            document.getElementById('imageCount').innerText = work.images.length;

            // Videos
            const vidWrap = document.getElementById('viewVideos');
            vidWrap.innerHTML = '';
            work.videos.forEach(video => {
                vidWrap.innerHTML += `
                    <div class="col-4">
                        <video src="${video.url}" controls class="w-100 rounded"></video>
                    </div>
                `;
            });

            document.getElementById('videoCount').innerText = work.videos.length;
        });
}
</script>
<script>
function editWork(id) {
    fetch(`/admin/ongoing-work/${id}`)
        .then(res => res.json())
        .then(work => {
            document.getElementById('editId').value = work.id;
            document.getElementById('editTitle').value = work.title;
            document.getElementById('editStatus').value = work.status;
            document.getElementById('editDescription').value = work.description;
            document.getElementById('editAddress').value = work.address;

            const imgWrap = document.getElementById('currentImages');
            imgWrap.innerHTML = '';
            work.images.forEach(img => {
                imgWrap.innerHTML += `<img src="${img.url}" class="preview-image">`;
            });

            const vidWrap = document.getElementById('currentVideos');
            vidWrap.innerHTML = '';
            work.videos.forEach(video => {
                vidWrap.innerHTML += `
                    <video src="${video.url}" class="preview-video" controls></video>
                `;
            });
        });
}
</script>
<script>
function updateWork() {
    const id = document.getElementById('editId').value;
    const form = document.getElementById('editWorkForm');
    const formData = new FormData(form);

    fetch(`/admin/ongoing-work/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            alert('Work updated successfully');
            location.reload(); // simple + safe for now
        }
    })
    .catch(err => {
        console.error(err);
        alert('Something went wrong');
    });
}
</script>
<script>
function setDeleteId(id) {
    deleteWorkId = id;

    fetch(`/admin/ongoing-work/${id}`)
        .then(res => res.json())
        .then(work => {
            document.getElementById('deleteTitle').innerText = work.title;
            document.getElementById('deleteId').innerText = work.id;
            document.getElementById('deleteStatus').innerText = work.status.replace('-', ' ');
        });
}
</script>
<script>
function deleteWork() {
    if (!deleteWorkId) return;

    fetch(`/admin/ongoing-work/${deleteWorkId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _method: 'DELETE'
        })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            alert('Work deleted successfully');
            location.reload(); // simple, reliable
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to delete work');
    });
}
</script>

@endsection