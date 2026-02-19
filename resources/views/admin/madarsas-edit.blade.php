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

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Students Count</label>
                    <input type="number" name="students_count" class="form-control" min="0"
                        value="{{ old('students_count', $madarsa->students_count) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Staff Count</label>
                    <input type="number" name="staff_count" class="form-control" min="0"
                        value="{{ old('staff_count', $madarsa->staff_count) }}" required>
                </div>

            </div>
        </div>

        <div class="form-section">
            <h5>Providing Courses</h5>

            @php
                $selectedCourses = old('courses', $madarsa->courses->pluck('id')->toArray());
            @endphp

            <div class="row">
                @foreach ($courses as $course)
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="courses[]" value="{{ $course->id }}"
                                id="course_{{ $course->id }}"
                                {{ in_array($course->id, $selectedCourses) ? 'checked' : '' }}>
                            <label class="form-check-label" for="course_{{ $course->id }}">
                                {{ $course->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-section">
            <h5>Madarsa Contact</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control"
                        value="{{ old('contact_number', $madarsa->contact_number) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Alternative Contact</label>
                    <input type="text" name="alternate_contact" class="form-control"
                        value="{{ old('alternate_contact', $madarsa->alternate_contact) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $madarsa->email) }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Website URL</label>
                    <input type="url" name="website_url" class="form-control"
                        value="{{ old('website_url', $madarsa->website_url) }}">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5>Donation Collectors</h5>

            <div id="collector-wrapper">

                @php
                    $oldCollectors = old('collectors', $madarsa->collectors->toArray());
                @endphp

                @foreach ($oldCollectors as $index => $collector)
                    <div class="collector-item border rounded p-3 mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="collectors[{{ $index }}][name]" class="form-control"
                                    value="{{ $collector['name'] }}" placeholder="Collector Name" required>
                            </div>

                            <div class="col-md-4">
                                <input type="text" name="collectors[{{ $index }}][contact]"
                                    class="form-control" value="{{ $collector['contact'] }}" placeholder="Contact"
                                    required>
                            </div>

                            <div class="col-md-3">
                                <input type="text" name="collectors[{{ $index }}][address]"
                                    class="form-control" value="{{ $collector['address'] ?? '' }}"
                                    placeholder="Address">
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-collector">X</button>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            <button type="button" class="btn btn-outline-primary" id="add-collector">
                Add Another Collector
            </button>
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
    <script>
        let collectorIndex = {{ count(old('collectors', $madarsa->collectors)) }};

        document.getElementById('add-collector').addEventListener('click', function() {

            const wrapper = document.getElementById('collector-wrapper');

            const html = `
            <div class="collector-item border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="collectors[${collectorIndex}][name]" class="form-control" placeholder="Collector Name" required>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="collectors[${collectorIndex}][contact]" class="form-control" placeholder="Contact" required>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="collectors[${collectorIndex}][address]" class="form-control" placeholder="Address">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-collector">X</button>
                    </div>
                </div>
            </div>
        `;

            wrapper.insertAdjacentHTML('beforeend', html);
            collectorIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-collector')) {
                e.target.closest('.collector-item').remove();
            }
        });
    </script>

@endsection
