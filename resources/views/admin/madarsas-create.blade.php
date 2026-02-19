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
                    <label class="form-label required-label">Community Type</label>
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

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Students Count</label>
                    <input type="number" name="students_count" class="form-control" min="0"
                        placeholder="Enter student count" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Staff Count</label>
                    <input type="number" name="staff_count" class="form-control" min="0"
                        placeholder="Enter staff count" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5><i class="fas fa-book me-2"></i>Providing Courses</h5>

            <div class="row">
                @foreach ($courses as $course)
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="courses[]" value="{{ $course->id }}"
                                id="course_{{ $course->id }}">
                            <label class="form-check-label" for="course_{{ $course->id }}">
                                {{ $course->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-section">
            <h5><i class="fas fa-phone me-2"></i>Madarsa Contact</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="Enter contact number"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Alternative Contact</label>
                    <input type="text" name="alternate_contact" class="form-control"
                        placeholder="Enter alternative number">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Website URL</label>
                    <input type="url" name="website_url" class="form-control" placeholder="Enter website URL">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h5><i class="fas fa-hand-holding-usd me-2"></i>Donation Collectors</h5>

            <div id="collector-wrapper">
                <div class="collector-item border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label required-label">Collector Name</label>
                            <input type="text" name="collectors[0][name]" class="form-control"
                                placeholder="Collector Name" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required-label">Contact</label>
                            <input type="text" name="collectors[0][contact]" class="form-control"
                                placeholder="Contact" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="collectors[0][address]" placeholder="Address"
                                class="form-control">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-collector">X</button>
                        </div>
                    </div>
                </div>
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
                    <input type="text" name="registration_number" class="form-control"
                        placeholder="Enter Registration No">
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

    <script>
        let collectorIndex = 1;

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
