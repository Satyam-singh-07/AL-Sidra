@extends('admin.layouts.app')

@section('title', 'Add New Madarsa')

@section('content')

    <style>
        .form-section {
            background: #fff;
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

        .required-label::after {
            content: " *";
            color: #dc3545;
        }
    </style>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Add New Madarsa</h2>
            <p class="text-muted mb-0">Register a new madarsa in the system</p>
        </div>
        <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <form action="{{ route('madarsas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Basic Information --}}
        <div class="form-section">
            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Madarsa Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter madarsa name">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="" selected disabled>Select gender</option>
                        <option value="male">Male (Boys)</option>
                        <option value="female">Female (Girls)</option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label required-label">Complete Address</label>

                    <input type="text" id="address" name="address" class="form-control" required
                        placeholder="Enter full address">
                </div>

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Community</label>
                    <select name="community_id" class="form-select" required>
                        <option value="" disabled selected>Select community</option>
                        @foreach ($communities as $community)
                            <option value="{{ $community->id }}">
                                {{ $community->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" selected>Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location</h5>

            <div id="map" style="height:300px;border-radius:8px;"></div>
        </div>

        {{-- Documents --}}
        <div class="form-section">
            <h5><i class="fas fa-file-alt me-2"></i>Documents</h5>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Donation Passbook</label>
                    <input type="file" name="passbook" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="registration_date" class="form-control">
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Madarsa Images</h5>

            <label class="form-label required-label">
                Upload Images (min 1, max 5)
            </label>

            <input type="file" name="madarsa_images[]" class="form-control" multiple required>

            <small class="text-muted">
                JPG / PNG only. Max 5 images.
            </small>
        </div>

        {{-- Video --}}
        <div class="form-section">
            <h5><i class="fas fa-video me-2"></i>Madarsa Video (Optional)</h5>

            <input type="file" name="video" class="form-control" accept="video/mp4,video/webm,video/ogg">
        </div>

        {{-- Actions --}}
        <div class="form-section">
            <div class="d-flex justify-content-between">
                <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Save Madarsa
                </button>
            </div>
        </div>

    </form>

    {{-- Google Maps --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap"
        async defer></script>

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
                map,
                draggable: true,
            });

            geocoder = new google.maps.Geocoder();

            const addressInput = document.getElementById("address");

            autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: {
                    country: "in"
                },
                fields: ["geometry", "formatted_address"],
            });

            autocomplete.addListener("place_changed", function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) return;

                const location = place.geometry.location;

                map.setCenter(location);
                map.setZoom(16);
                marker.setPosition(location);

                document.getElementById("latitude").value = location.lat();
                document.getElementById("longitude").value = location.lng();
            });

            marker.addListener("dragend", function() {
                const pos = marker.getPosition();
                document.getElementById("latitude").value = pos.lat();
                document.getElementById("longitude").value = pos.lng();
            });
        }
    </script>

@endsection
