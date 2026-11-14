@extends('admin.layouts.app')

@section('title', 'Adhan Audios')

@section('content')

    <style>
        .audio-player {
            height: 38px;
            max-width: 260px;
        }

        .action-btns .btn {
            margin-right: 5px;
        }

        .action-btns .btn:last-child {
            margin-right: 0;
        }
    </style>

    <div class="container-fluid p-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-success fw-bold mb-1">Adhan Audio Library</h2>
                <p class="text-muted mb-0">Upload and manage official Adhan recordings available for Masjid Mutawallis.</p>
            </div>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdhanModal">
                <i class="fas fa-plus me-2"></i> Add Adhan Audio
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Reciter / Muadhin</th>
                                <th>Audio Preview</th>
                                <th>Masjids Using</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adhanAudios as $audio)
                                <tr>
                                    <td>{{ $audio->id }}</td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $audio->title }}</span>
                                        @if($audio->duration)
                                            <div class="text-muted small"><i class="fas fa-clock me-1"></i>{{ $audio->duration }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $audio->reciter ?? '—' }}</td>
                                    <td>
                                        <audio controls preload="none" class="audio-player">
                                            <source src="{{ $audio->audio_url }}" type="audio/mpeg">
                                            Your browser does not support audio element.
                                        </audio>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-mosque me-1 text-success"></i> {{ $audio->masjids_count }} Masjids
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $audio->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($audio->status) }}
                                        </span>
                                    </td>
                                    <td class="action-btns">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editAdhanModal{{ $audio->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteAdhanModal{{ $audio->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editAdhanModal{{ $audio->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('adhan-audios.update', $audio->id) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Adhan: {{ $audio->title }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Title *</label>
                                                        <input type="text" name="title" value="{{ old('title', $audio->title) }}"
                                                            class="form-control" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Reciter / Muadhin</label>
                                                        <input type="text" name="reciter" value="{{ old('reciter', $audio->reciter) }}"
                                                            class="form-control" placeholder="e.g. Sheikh Ali Ahmad Mullah">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Duration</label>
                                                        <input type="text" name="duration" value="{{ old('duration', $audio->duration) }}"
                                                            class="form-control" placeholder="e.g. 03:20">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Current Audio</label>
                                                        <div>
                                                            <audio controls preload="none" class="audio-player w-100">
                                                                <source src="{{ $audio->audio_url }}" type="audio/mpeg">
                                                            </audio>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Replace Audio File (Optional)</label>
                                                        <input type="file" name="audio_file" class="form-control"
                                                            accept=".mp3,.wav,.ogg,.aac,.m4a">
                                                        <div class="form-text">MP3, WAV, OGG, AAC, M4A up to 15MB</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="active" @selected($audio->status === 'active')>Active (Visible to Mutawallis)</option>
                                                            <option value="inactive" @selected($audio->status === 'inactive')>Inactive (Hidden)</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Update Adhan Audio</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Delete Modal --}}
                                <div class="modal fade" id="deleteAdhanModal{{ $audio->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('adhan-audios.destroy', $audio->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Delete Adhan Audio</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p class="mb-2">Are you sure you want to delete <strong>{{ $audio->title }}</strong>?</p>
                                                    @if($audio->masjids_count > 0)
                                                        <div class="alert alert-warning mb-0 small">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            This audio is currently used by <strong>{{ $audio->masjids_count }}</strong> Masjid(s).
                                                            Deleting it will revert those Masjids to the standard default adhan tone.
                                                        </div>
                                                    @else
                                                        <p class="text-muted small mb-0">This file will be permanently removed.</p>
                                                    @endif
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Delete Audio</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-volume-mute fa-2x mb-2 d-block text-secondary"></i>
                                        No Adhan audios found. Click <strong>Add Adhan Audio</strong> to upload the first one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $adhanAudios->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add Adhan Modal --}}
    <div class="modal fade" id="addAdhanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success fw-bold">Add New Adhan Audio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('adhan-audios.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Makkah Al-Mukarramah Adhan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reciter / Muadhin</label>
                            <input type="text" name="reciter" class="form-control" placeholder="e.g. Sheikh Ali Ahmad Mullah">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (Optional)</label>
                            <input type="text" name="duration" class="form-control" placeholder="e.g. 03:25">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Audio File *</label>
                            <input type="file" name="audio_file" class="form-control" accept=".mp3,.wav,.ogg,.aac,.m4a" required>
                            <div class="form-text">Supported: MP3, WAV, OGG, AAC, M4A (Max 15MB)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active (Available to Mutawallis)</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save & Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
