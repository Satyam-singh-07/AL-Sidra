@extends('admin.layouts.app')

@section('title', 'Edit Masjid')

@section('content')

    <style>
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section h5 {
            color: #198754;
            border-bottom: 2px solid #198754;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .file-upload {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
        }

        .file-upload i {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .file-preview {
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
        }

        .location-map {
            height: 200px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            margin-top: 15px;
        }

        .required-label:after {
            content: " *";
            color: #dc3545;
        }

        .masjid-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Edit Masjid</h2>
            <p class="text-muted mb-0">Edit a mosque in the system</p>
        </div>
        <div>
            <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>


    <!-- Main Form -->
    <form action="{{ route('masjids.update', $masjid) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')


        <!-- Section 1: Basic Information -->
        <div class="form-section">
            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="masjidName" class="form-label required-label">Masjid Name</label>
                    <input type="text" value="{{ old('name', $masjid->name) }}" class="form-control" name="name"
                        required placeholder="Enter masjid or madarsa name">
                    <div class="invalid-feedback">
                        Please enter masjid name.
                    </div>
                </div>


                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label required-label">Complete Address</label>
                    <textarea id="address" class="form-control" name="address" rows="3" required
                        placeholder="Enter full address with landmark">{{ old('address', $masjid->address ?? '') }}</textarea>


                    <input type="hidden" name="latitude" value="{{ old('latitude', $masjid->latitude) }}" id="latitude">
                    <input type="hidden" name="longitude" value="{{ old('longitude', $masjid->longitude) }}"
                        id="longitude">
                    <div class="invalid-feedback">
                        Please enter complete address.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="community" class="form-label required-label">Community</label>

                    <select name="community_id" id="community" class="form-select @error('community') is-invalid @enderror"
                        required>
                        <option value="">-- Select community --</option>

                        @foreach ($communities as $community)
                            <option value="{{ $community->id }}"
                                {{ old('community', $masjid->community_id ?? null) == $community->id ? 'selected' : '' }}>
                                {{ $community->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('community')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>



                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label required-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $masjid->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ old('status', $masjid->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="inactive" {{ old('status', $masjid->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Location Details -->
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location Details</h5>

            <div class="row">

                <div class="col-12">
                    <label class="form-label">Map Preview</label>
                    <div id="map" class="location-map" style="height:300px;"></div>
                </div>
            </div>
        </div>

        <!-- Section 3: Documents -->
        <div class="form-section">
            <h5><i class="fas fa-file-alt me-2"></i>Documents</h5>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label">Donation Passbook</label>
                    @if($masjid->passbook)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $masjid->passbook) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye me-1"></i> View Current Passbook
                            </a>
                        </div>
                    @endif
                    <div class="file-upload" onclick="document.getElementById('passbookFile').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h5>Upload New Passbook Copy</h5>
                        <p class="text-muted">Click to upload or drag and drop</p>
                        <small class="text-muted">PDF, JPG, PNG files up to 5MB</small>
                    </div>
                    <input type="file" id="passbookFile" name="passbook" class="form-control d-none @error('passbook') is-invalid @enderror">
                    @error('passbook')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="regNumber" class="form-label">Registration Number</label>
                    <input type="text" class="form-control"
                        value="{{ old('registration_number', $masjid->registration_number) }}" id="regNumber"
                        name="registration_number" placeholder="If registered with government">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="regDate" class="form-label">Registration Date</label>
                    <input type="date" class="form-control"
                        value="{{ old('registration_date', $masjid->registration_date) }}" id="regDate"
                        name="registration_date">
                </div>
            </div>
        </div>

        <!-- Section: Existing Images -->
        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Existing Images</h5>
            <div class="row">
                @forelse($masjid->images as $image)
                    <div class="col-md-3 mb-3">
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="masjid-img">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                onclick="if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-muted ms-3">No images uploaded</p>
                @endforelse
            </div>
        </div>

        <!-- Section 4: Masjid Images -->
        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Add New Images</h5>

            <label class="form-label">
                Upload New Images
            </label>

            <input type="file" name="masjid_images[]"
                class="form-control @error('masjid_images') is-invalid @enderror" multiple>

            <small class="text-muted">
                JPG, PNG only. Max 5 images. Each image should be under 5MB.
            </small>
        </div>

        <!-- Section 5: Video -->
        <div class="form-section">
            <h5><i class="fas fa-video me-2"></i>Masjid Video</h5>

            @if($masjid->video)
                <div class="mb-3">
                    <video width="320" height="240" controls>
                        <source src="{{ asset('storage/' . $masjid->video) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="mt-2">
                        <button type="button" class="btn btn-danger btn-sm"
                            onclick="if(confirm('Delete this video?')) document.getElementById('delete-video-form').submit();">
                            <i class="fas fa-trash me-1"></i> Remove Video
                        </button>
                    </div>
                </div>
            @endif

            <label for="masjidVideo" class="form-label">Upload New Video (Optional)</label>
            <input type="file" class="form-control" id="masjidVideo" name="masjid_video"
                accept="video/mp4,video/webm,video/ogg">
            <small class="text-muted">
                Only one video allowed. MP4 / WebM / OGG. Max 20MB.
            </small>
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-between">
                <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <div>
                    <button type="reset" class="btn btn-outline-danger me-2">
                        <i class="fas fa-redo me-2"></i>Reset Form
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Update Masjid
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Hidden Delete Forms --}}
    @foreach ($masjid->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route('masjids.delete-image', $image->id) }}"
            method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @if($masjid->video)
        <form id="delete-video-form" action="{{ route('masjids.delete-video', $masjid) }}"
            method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap"
        async defer></script>

    <script>
        let map, marker, autocomplete, geocoder;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);

            const location = {
                lat: lat || 20.5937,
                lng: lng || 78.9629
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: lat && lng ? 16 : 5,
            });

            marker = new google.maps.Marker({
                map: map,
                position: location,
                draggable: true,
            });

            geocoder = new google.maps.Geocoder();

            const addressInput = document.getElementById('address');

            autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: {
                    country: "in"
                },
                fields: ["geometry", "formatted_address"],
            });

            autocomplete.addListener("place_changed", function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) {
                    geocodeAddress(addressInput.value);
                    return;
                }

                updateLocation(place.geometry.location);
            });

            marker.addListener("dragend", function() {
                updateLocation(marker.getPosition());
            });
        }

        function updateLocation(location) {
            map.setCenter(location);
            map.setZoom(16);
            marker.setPosition(location);

            document.getElementById("latitude").value = location.lat();
            document.getElementById("longitude").value = location.lng();
        }

        function geocodeAddress(address) {
            if (!address) return;

            geocoder.geocode({
                address
            }, function(results, status) {
                if (status === "OK") {
                    updateLocation(results[0].geometry.location);
                }
            });
        }
    </script>


@endsection
