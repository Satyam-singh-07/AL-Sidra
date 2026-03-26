@extends('admin.layouts.app')

@section('title', 'Edit Restaurant')

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

        .restaurant-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-success fw-bold mb-1">Edit Restaurant</h2>
            <p class="text-muted mb-0">Update restaurant details</p>
        </div>
        <div>
            <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('restaurants.update', $restaurant) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div class="form-section">
            <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Restaurant Name</label>
                    <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $restaurant->contact_number) }}" class="form-control @error('contact_number') is-invalid @enderror" required>
                    @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $restaurant->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ old('opening_time', $restaurant->opening_time ? date('H:i', strtotime($restaurant->opening_time)) : '') }}" class="form-control @error('opening_time') is-invalid @enderror" required>
                    @error('opening_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Closing Time</label>
                    <input type="time" name="closing_time" value="{{ old('closing_time', $restaurant->closing_time ? date('H:i', strtotime($restaurant->closing_time)) : '') }}" class="form-control @error('closing_time') is-invalid @enderror" required>
                    @error('closing_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status', $restaurant->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status', $restaurant->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status', $restaurant->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Section 2: Location -->
        <div class="form-section">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Location Details</h5>
            <div class="mb-3">
                <label class="form-label required-label">Full Address</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $restaurant->address) }}</textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $restaurant->latitude) }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $restaurant->longitude) }}">

            <div id="map" class="location-map"></div>
        </div>

        <!-- Section 3: Menu & Video -->
        <div class="form-section">
            <h5><i class="fas fa-file-image me-2"></i>Menu & Video</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Menu Image</label>
                    @if($restaurant->menu_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $restaurant->menu_image) }}" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif
                    <input type="file" name="menu_image" class="form-control @error('menu_image') is-invalid @enderror">
                    <small class="text-muted">Upload new image to replace current one (JPG, PNG, max 2MB)</small>
                    @error('menu_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Restaurant Video</label>
                    @if($restaurant->video)
                        <div class="mb-2">
                            <video width="200" controls>
                                <source src="{{ asset('storage/' . $restaurant->video) }}" type="video/mp4">
                            </video>
                            <div class="mt-1">
                                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Delete video?')) document.getElementById('delete-video-form').submit();">
                                    <i class="fas fa-trash me-1"></i> Remove Video
                                </button>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="video" class="form-control @error('video') is-invalid @enderror">
                    <small class="text-muted">Upload new video (MP4, max 20MB)</small>
                    @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Section 4: Images Gallery -->
        <div class="form-section">
            <h5><i class="fas fa-images me-2"></i>Gallery Images</h5>
            
            <div class="row mb-4">
                @forelse($restaurant->images as $image)
                    <div class="col-md-3 mb-3">
                        <div class="position-relative">
                            <img src="{{ asset('storage/' . $image->image) }}" class="restaurant-img">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                onclick="if(confirm('Delete this image?')) document.getElementById('delete-image-{{ $image->id }}').submit();">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><p class="text-muted">No gallery images uploaded yet.</p></div>
                @endforelse
            </div>

            <label class="form-label">Add More Images</label>
            <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror" multiple>
            <small class="text-muted">Select multiple images (JPG, PNG, max 2MB each)</small>
            @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Form Actions -->
        <div class="form-section">
            <div class="d-flex justify-content-between">
                <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Update Restaurant
                </button>
            </div>
        </div>
    </form>

    {{-- Hidden Delete Forms --}}
    @foreach ($restaurant->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route('restaurants.delete-image', $image->id) }}"
            method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @if($restaurant->video)
        <form id="delete-video-form" action="{{ route('restaurants.delete-video', $restaurant) }}"
            method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initMap" async defer></script>

    <script>
        let map, marker, autocomplete, geocoder;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 20.5937;
            const lng = parseFloat(document.getElementById('longitude').value) || 78.9629;
            const location = { lat, lng };

            map = new google.maps.Map(document.getElementById("map"), {
                center: location,
                zoom: (document.getElementById('latitude').value) ? 16 : 5,
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
