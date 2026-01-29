@extends('admin.layouts.app')

@section('title', 'Add New Masjid')

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
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Add New Masjid</h2>
            <p class="text-muted mb-0">Register a new mosque or madarsa in the system</p>
        </div>
        <div>
            <a href="{{ route('masjids.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            {{-- <button type="submit" form="masjidForm" class="btn btn-success">
                <i class="fas fa-save me-2"></i>Save Masjid
            </button> --}}
        </div>
    </div>


    <!-- Main Form -->
    <form action="{{ route('masjids.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Basic Information -->
        <div class="form-section">
            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="masjidName" class="form-label required-label">Masjid Name</label>
                    <input type="text" class="form-control" name="name" required
                        placeholder="Enter masjid or madarsa name">
                    <div class="invalid-feedback">
                        Please enter masjid name.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label required-label">Gender</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Select gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <!-- <div class="col-md-6 mb-3">
                                                                                                            <label for="masjidType" class="form-label required-label">Type</label>
                                                                                                            <select class="form-select" id="masjidType" required>
                                                                                                                <option value="" selected disabled>Select type</option>
                                                                                                                <option value="masjid">Masjid (Mosque)</option>
                                                                                                                <option value="madarsa">Madarsa (Islamic School)</option>
                                                                                                                <option value="center">Islamic Center</option>
                                                                                                                <option value="dargah">Dargah (Shrine)</option>
                                                                                                            </select>
                                                                                                            <div class="invalid-feedback">
                                                                                                                Please select masjid type.
                                                                                                            </div>
                                                                                                        </div> -->

                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label required-label">Complete Address</label>
                    <textarea class="form-control" name="address" rows="3" required placeholder="Enter full address with landmark"></textarea>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <div class="invalid-feedback">
                        Please enter complete address.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="community_id" class="form-label required-label">Community</label>

                    <select class="form-select" name="community_id" id="community_id" required>
                        <option value="" disabled selected>Select community</option>

                        @foreach ($communities as $community)
                            <option value="{{ $community->id }}"
                                {{ old('community_id') == $community->id ? 'selected' : '' }}>
                                {{ $community->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback">
                        Please select community.
                    </div>
                </div>


                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label required-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Location Details -->
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location Details</h5>

            <div class="row">
                <!-- <div class="col-md-6 mb-3">
                                                                                                            <label for="latitude" class="form-label">Latitude</label>
                                                                                                            <div class="input-group">
                                                                                                                <input type="number" step="any" class="form-control" id="latitude"
                                                                                                                       placeholder="e.g., 28.6139">
                                                                                                                <button class="btn btn-outline-secondary" type="button"
                                                                                                                        onclick="getCurrentLocation()">
                                                                                                                    <i class="fas fa-location-crosshairs"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                            <small class="text-muted">Click the location icon to get current coordinates</small>
                                                                                                        </div>
                                                                                                        
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                            <label for="longitude" class="form-label">Longitude</label>
                                                                                                            <input type="number" step="any" class="form-control" id="longitude"
                                                                                                                   placeholder="e.g., 77.2090">
                                                                                                        </div> -->

                <div class="col-12">
                    <label class="form-label">Map Preview</label>
                    <div id="map" class="location-map" style="height:300px;"></div>
                    @php
                        $address = old('address');
                    @endphp

                    <script
                        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap"
                        async defer></script>
                </div>
            </div>
        </div>

        <!-- Section 3: Documents -->
        <div class="form-section">
            <h5><i class="fas fa-file-alt me-2"></i>Documents</h5>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <label class="form-label required-label">Donation Passbook</label>
                    <div class="file-upload" onclick="document.getElementById('passbookFile').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h5>Upload Passbook Copy</h5>
                        <p class="text-muted">Click to upload or drag and drop</p>
                        <small class="text-muted">PDF, JPG, PNG files up to 5MB</small>
                    </div>
                    <input type="file" name="passbook" class="form-control @error('passbook') is-invalid @enderror">
                    @error('passbook')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror


                    <!-- File Preview -->
                    <div id="filePreview" class="file-preview d-none">
                        <div class="alert alert-success d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <span id="fileName">donation_passbook.pdf</span>
                                <small class="text-muted ms-2" id="fileSize">(2.4 MB)</small>
                            </div>
                            <button type="button" class="btn-close" onclick="removeFile()"></button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="regNumber" class="form-label">Registration Number</label>
                    <input type="text" class="form-control" id="regNumber" name="registration_number"
                        placeholder="If registered with government">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="regDate" class="form-label">Registration Date</label>
                    <input type="date" class="form-control" id="regDate" name="registration_date">
                </div>
            </div>
        </div>

        <!-- Section 4: Masjid Images -->
        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Masjid Images</h5>

            <label class="form-label required-label">
                Upload Images (1 required, max 5)
            </label>

            <input type="file" name="masjid_images[]"
                class="form-control @error('masjid_images') is-invalid @enderror" multiple>

            <div class="invalid-feedback">
                Please upload at least one image (maximum 5 allowed).
            </div>

            <!-- Preview -->
            <div class="row mt-3" id="imagePreview"></div>

            <small class="text-muted">
                JPG, PNG only. Max 5 images. Each image should be under 5MB.
            </small>
        </div>

        <div class="col-md-12 mb-3">
            <label for="masjidVideo" class="form-label">Masjid Video (Optional)</label>
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
                        <i class="fas fa-save me-2"></i>Save Masjid
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        let map, marker, autocomplete, geocoder;

        function initMap() {
            const defaultLocation = {
                lat: 20.5937,
                lng: 78.9629
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLocation,
                zoom: 5,
            });

            marker = new google.maps.Marker({
                map: map,
                draggable: true,
            });

            geocoder = new google.maps.Geocoder();

            const addressInput = document.querySelector('textarea[name="address"]');

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

                const location = place.geometry.location;

                map.setCenter(location);
                map.setZoom(16);
                marker.setPosition(location);

                document.getElementById("latitude").value = location.lat();
                document.getElementById("longitude").value = location.lng();
            });

            addressInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    geocodeAddress(addressInput.value);
                }
            });

            marker.addListener("dragend", function() {
                const pos = marker.getPosition();
                document.getElementById("latitude").value = pos.lat();
                document.getElementById("longitude").value = pos.lng();
            });
        }

        function geocodeAddress(address) {
            if (!address) return;

            geocoder.geocode({
                address: address
            }, function(results, status) {
                if (status === "OK") {
                    const location = results[0].geometry.location;

                    map.setCenter(location);
                    map.setZoom(16);
                    marker.setPosition(location);

                    document.getElementById("latitude").value = location.lat();
                    document.getElementById("longitude").value = location.lng();
                }
            });
        }
    </script>



@endsection
