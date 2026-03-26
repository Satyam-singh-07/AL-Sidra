@extends('admin.layouts.app')

@section('title', 'Create Restaurant')

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

        .location-map {
            height: 300px;
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
            <h2 class="text-success fw-bold mb-1">Add New Restaurant</h2>
            <p class="text-muted mb-0">Create a new restaurant entry</p>
        </div>
        <div>
            <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('restaurants.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Basic Information -->
        <div class="form-section">
            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Restaurant Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required placeholder="Enter restaurant name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control @error('contact_number') is-invalid @enderror" required placeholder="Enter contact number">
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Enter restaurant description">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ old('opening_time') }}" class="form-control @error('opening_time') is-invalid @enderror" required>
                    @error('opening_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Closing Time</label>
                    <input type="time" name="closing_time" value="{{ old('closing_time') }}" class="form-control @error('closing_time') is-invalid @enderror" required>
                    @error('closing_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Location -->
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location Details</h5>
            <div class="mb-3">
                <label class="form-label required-label">Full Address</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required placeholder="Search or enter full address">{{ old('address') }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            <div id="map" class="location-map"></div>
        </div>

        <!-- Section 3: Media -->
        <div class="form-section">
            <h5><i class="fas fa-file-image me-2"></i>Menu & Video</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Menu Image</label>
                    <input type="file" name="menu_image" class="form-control @error('menu_image') is-invalid @enderror">
                    @error('menu_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Restaurant Video</label>
                    <input type="file" name="video" class="form-control @error('video') is-invalid @enderror">
                    @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Gallery Images</h5>
            <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror" multiple>
            <small class="text-muted">You can select multiple images</small>
            @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-between">
                <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Create Restaurant
                </button>
            </div>
        </div>
    </form>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap" async defer></script>

    <script>
        let map, marker, autocomplete, geocoder;

        function initMap() {
            const location = { lat: 20.5937, lng: 78.9629 }; // Default India

            map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: 5,
            });

            marker = new google.maps.Marker({
                map: map,
                position: location,
                draggable: true,
            });

            geocoder = new google.maps.Geocoder();
            const addressInput = document.getElementById('address');

            autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: { country: "in" },
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
            geocoder.geocode({ address }, function(results, status) {
                if (status === "OK") {
                    updateLocation(results[0].geometry.location);
                }
            });
        }
    </script>
@endsection
