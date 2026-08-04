@extends('layouts.app')

@section('title', 'New Property')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">Properties · Fast Create</span>
        <h2 class="h3 mt-1 mb-0">Create property</h2>
        <p class="text-muted small mb-0">Name and address are all that is required — coordinates resolve in the background.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('properties.store') }}" class="reveal" style="--d: 80ms">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Required</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror" required autocomplete="off" placeholder="Start typing for Google suggestions…">
                        <div id="place-suggestions" class="list-group position-absolute w-100" style="z-index: 1050; display: none;"></div>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0"><i class="bi bi-geo me-1" aria-hidden="true"></i>Suggestions load from Google Places (server-side key).</p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <button type="button" class="card-header mono w-100 text-start bg-transparent border-0" data-bs-toggle="collapse" data-bs-target="#optionalSection" aria-expanded="false" aria-controls="optionalSection">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Optional details
            </button>
            <div id="optionalSection" class="collapse">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="formatted_address" class="form-label">Formatted address</label>
                            <input type="text" id="formatted_address" name="formatted_address" value="{{ old('formatted_address') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="google_place_id" class="form-label">Google Place ID</label>
                            <input type="text" id="google_place_id" name="google_place_id" value="{{ old('google_place_id') }}" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude') }}" class="form-control" placeholder="—">
                        </div>
                        <div class="col-md-3">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude') }}" class="form-control" placeholder="—">
                        </div>
                        <div class="col-md-3">
                            <label for="property_category_id" class="form-label">Category</label>
                            <select name="property_category_id" id="property_category_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('property_category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="permitted_check_in_radius_meters" class="form-label">Check-in radius (m)</label>
                            <input type="number" min="0" id="permitted_check_in_radius_meters" name="permitted_check_in_radius_meters" value="{{ old('permitted_check_in_radius_meters') }}" class="form-control" placeholder="Fallback chain applies">
                        </div>
                        <div class="col-12">
                            <label for="tags" class="form-label">Tags</label>
                            <select name="tags[]" id="tags" class="form-select" multiple size="4">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}" @selected(in_array($tag->id, old('tags', [])))>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="contact_name" class="form-label">Contact name</label>
                            <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="contact_phone" class="form-label">Contact phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="contact_email" class="form-label">Contact email</label>
                            <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Postal code</label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="service_frequency" class="form-label">Service frequency</label>
                            <select name="service_frequency" id="service_frequency" class="form-select">
                                <option value="">None</option>
                                @foreach (['daily', 'weekly', 'fortnightly', 'monthly', 'quarterly', 'one_off'] as $freq)
                                    <option value="{{ $freq }}" @selected(old('service_frequency') === $freq)>{{ ucfirst(str_replace('_', ' ', $freq)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', true))>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="access_instructions" class="form-label">Access instructions</label>
                            <textarea id="access_instructions" name="access_instructions" rows="3" class="form-control">{{ old('access_instructions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="parking_instructions" class="form-label">Parking instructions</label>
                            <textarea id="parking_instructions" name="parking_instructions" rows="3" class="form-control">{{ old('parking_instructions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="safety_instructions" class="form-label">Safety instructions</label>
                            <textarea id="safety_instructions" name="safety_instructions" rows="3" class="form-control">{{ old('safety_instructions') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="special_cleaning_requirements" class="form-label">Special cleaning requirements</label>
                            <textarea id="special_cleaning_requirements" name="special_cleaning_requirements" rows="3" class="form-control">{{ old('special_cleaning_requirements') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="internal_notes" class="form-label">Internal notes</label>
                            <textarea id="internal_notes" name="internal_notes" rows="2" class="form-control">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Map preview</div>
            <div class="card-body">
                <div id="map" style="height: 320px; border-radius: 4px; background: var(--cw-canvas, #eef1f5);"></div>
                <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Drag the pin to adjust coordinates manually.</p>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-building-check me-1" aria-hidden="true"></i>Create property
        </button>
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function ($) {
            var $address = $('#address'), $suggestions = $('#place-suggestions');
            var $lat = $('#latitude'), $lng = $('#longitude');
            var $placeId = $('#google_place_id'), $formatted = $('#formatted_address');
            var debounceTimer = null;

            var map = null, marker = null;

            function initMap(lat, lng) {
                if (!window.L) return;
                if (!map) {
                    map = L.map('map').setView([lat || -40.9, lng || 174.9], lat ? 16 : 5);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);
                }
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                map.setView([lat, lng], 16);
                marker.on('dragend', function () {
                    var p = marker.getLatLng();
                    $lat.val(p.lat.toFixed(7));
                    $lng.val(p.lng.toFixed(7));
                });
            }

            $address.on('input', function () {
                clearTimeout(debounceTimer);
                var input = $(this).val().trim();
                if (input.length < 3) { $suggestions.hide(); return; }
                debounceTimer = setTimeout(function () {
                    axios.get('{{ route('places.autocomplete') }}', { params: { input: input } })
                        .then(function (res) {
                            var items = res.data.data || [];
                            if (!items.length) { $suggestions.hide(); return; }
                            $suggestions.empty().show();
                            items.forEach(function (item) {
                                $('<button type="button" class="list-group-item list-group-item-action py-2 small text-start"></button>')
                                    .text(item.description)
                                    .on('click', function () { selectPlace(item); })
                                    .appendTo($suggestions);
                            });
                        })
                        .catch(function () { $suggestions.hide(); });
                }, 300);
            });

            function selectPlace(item) {
                $suggestions.hide();
                $address.val(item.description);
                $formatted.val(item.description);
                $placeId.val(item.place_id);
                axios.get('{{ route('places.details') }}', { params: { place_id: item.place_id } })
                    .then(function (res) {
                        var d = res.data.data;
                        if (!d) return;
                        $formatted.val(d.formatted_address || $formatted.val());
                        $lat.val(d.latitude);
                        $lng.val(d.longitude);
                        initMap(d.latitude, d.longitude);
                    })
                    .catch(function () {});
            }

            function syncFromInputs() {
                var lat = parseFloat($lat.val()), lng = parseFloat($lng.val());
                if (isFinite(lat) && isFinite(lng)) initMap(lat, lng);
            }
            $lat.on('change', syncFromInputs);
            $lng.on('change', syncFromInputs);

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#place-suggestions, #address').length) $suggestions.hide();
            });

            if ($lat.val() && $lng.val()) syncFromInputs();
        })(jQuery);
    </script>
@endpush
