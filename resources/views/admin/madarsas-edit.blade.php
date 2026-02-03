@extends('admin.layouts.app')

@section('title', 'Edit Madarsa')

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

        .madarsa-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .required-label::after {
            content: " *";
            color: #dc3545;
        }
    </style>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Edit Madarsa</h2>
            <p class="text-muted mb-0">{{ $madarsa->name }}</p>
        </div>

        <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route('madarsas.update', $madarsa) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="form-section">
            <h5>Basic Information</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Madarsa Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $madarsa->name) }}"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="male" {{ old('gender', $madarsa->gender) == 'male' ? 'selected' : '' }}>
                            Male (Boys)
                        </option>
                        <option value="female" {{ old('gender', $madarsa->gender) == 'female' ? 'selected' : '' }}>
                            Female (Girls)
                        </option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label required-label">Address</label>
                    <div class="col-md-12 mb-3">
                        <label class="form-label required-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control"
                            value="{{ old('address', $madarsa->address) }}" required>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $madarsa->latitude) }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $madarsa->longitude) }}">

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Community</label>
                    <select name="community_id" class="form-select" required>
                        @foreach ($communities as $community)
                            <option value="{{ $community->id }}"
                                {{ old('community_id', $madarsa->community_id) == $community->id ? 'selected' : '' }}>
                                {{ $community->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $madarsa->status) == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="pending" {{ old('status', $madarsa->status) == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="inactive" {{ old('status', $madarsa->status) == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location</h5>

            <div id="map" style="height:300px;border-radius:8px;"></div>
        </div>

        {{-- Existing Images --}}
        <div class="form-section">
            <h5>Existing Images</h5>

            <div class="row">
                @forelse($madarsa->images as $image)
                    <div class="col-md-3 mb-3">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="madarsa-img">
                    </div>
                @empty
                    <p class="text-muted">No images uploaded</p>
                @endforelse
            </div>
        </div>

        {{-- Upload New Images --}}
        <div class="form-section">
            <h5>Add New Images</h5>

            <input type="file" name="madarsa_images[]" class="form-control" multiple>

            <small class="text-muted">
                Uploading new images will NOT remove existing ones.
            </small>
        </div>

        {{-- Documents --}}
        <div class="form-section">
            <h5>Documents</h5>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Passbook</label>
                    <input type="file" name="passbook" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control"
                        value="{{ old('registration_number', $madarsa->registration_number) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="registration_date" class="form-control"
                        value="{{ old('registration_date', $madarsa->registration_date) }}">
                </div>
            </div>
        </div>

        {{-- Video --}}
        <div class="form-section">
            <h5>Madarsa Video</h5>

            <input type="file" name="video" class="form-control" accept="video/mp4,video/webm,video/ogg">
        </div>

        {{-- Actions --}}
        <div class="form-section">
            <div class="d-flex justify-content-between">
                <a href="{{ route('madarsas.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Update Madarsa
                </button>
            </div>
        </div>

    </form>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap"
        async defer></script>

    <script>
        let map, marker, autocomplete, geocoder;

        function initMap() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');

            const lat = parseFloat(latInput.value) || 20.5937;
            const lng = parseFloat(lngInput.value) || 78.9629;

            const location = {
                lat,
                lng
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: (latInput.value && lngInput.value) ? 16 : 5,
            });

            marker = new google.maps.Marker({
                map,
                position: location,
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

                updateLocation(place.geometry.location);
                addressInput.value = place.formatted_address;
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
    </script>

@endsection
