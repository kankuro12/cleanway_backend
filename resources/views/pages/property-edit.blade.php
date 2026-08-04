@extends('layouts.app')

@section('title', 'Edit '.$property->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Properties · {{ $property->uuid }}</span>
            <h2 class="h3 mt-1 mb-0">{{ $property->name }}</h2>
        </div>
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to registry
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('properties.update', $property) }}" class="reveal" style="--d: 80ms">
                @csrf
                @method('PUT')
                <div class="card shadow-sm mb-3">
                    <div class="card-header mono">Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $property->name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" id="address" name="address" value="{{ old('address', $property->address) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="formatted_address" class="form-label">Formatted address</label>
                                <input type="text" id="formatted_address" name="formatted_address" value="{{ old('formatted_address', $property->formatted_address) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="google_place_id" class="form-label">Google Place ID</label>
                                <input type="text" id="google_place_id" name="google_place_id" value="{{ old('google_place_id', $property->google_place_id) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $property->latitude) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $property->longitude) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="property_category_id" class="form-label">Category</label>
                                <select name="property_category_id" id="property_category_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('property_category_id', $property->property_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="permitted_check_in_radius_meters" class="form-label">Check-in radius (m)</label>
                                <input type="number" min="0" id="permitted_check_in_radius_meters" name="permitted_check_in_radius_meters" value="{{ old('permitted_check_in_radius_meters', $property->permitted_check_in_radius_meters) }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="tags" class="form-label">Tags</label>
                                <select name="tags[]" id="tags" class="form-select" multiple size="4">
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}" @selected($property->tags->contains('id', $tag->id))>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="contact_name" class="form-label">Contact name</label>
                                <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', $property->contact_name) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="contact_phone" class="form-label">Contact phone</label>
                                <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $property->contact_phone) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="contact_email" class="form-label">Contact email</label>
                                <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $property->contact_email) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postal code</label>
                                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $property->postal_code) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="service_frequency" class="form-label">Service frequency</label>
                                <select name="service_frequency" id="service_frequency" class="form-select">
                                    <option value="">None</option>
                                    @foreach (['daily', 'weekly', 'fortnightly', 'monthly', 'quarterly', 'one_off'] as $freq)
                                        <option value="{{ $freq }}" @selected(old('service_frequency', $property->service_frequency) === $freq)>{{ ucfirst(str_replace('_', ' ', $freq)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', $property->active))>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="access_instructions" class="form-label">Access instructions</label>
                                <textarea id="access_instructions" name="access_instructions" rows="3" class="form-control">{{ old('access_instructions', $property->access_instructions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="parking_instructions" class="form-label">Parking instructions</label>
                                <textarea id="parking_instructions" name="parking_instructions" rows="3" class="form-control">{{ old('parking_instructions', $property->parking_instructions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="safety_instructions" class="form-label">Safety instructions</label>
                                <textarea id="safety_instructions" name="safety_instructions" rows="3" class="form-control">{{ old('safety_instructions', $property->safety_instructions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="special_cleaning_requirements" class="form-label">Special cleaning requirements</label>
                                <textarea id="special_cleaning_requirements" name="special_cleaning_requirements" rows="3" class="form-control">{{ old('special_cleaning_requirements', $property->special_cleaning_requirements) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="internal_notes" class="form-label">Internal notes</label>
                                <textarea id="internal_notes" name="internal_notes" rows="2" class="form-control">{{ old('internal_notes', $property->internal_notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save changes
                    </button>
                    @if(auth()->user()->hasPermission('3.3'))
                        <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Archive this property?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-archive me-1" aria-hidden="true"></i>Archive
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-3 reveal" style="--d: 120ms">
                <div class="card-header mono">Geocoding</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="status-badge status-{{ $property->geocode_status === 'resolved' ? 'active' : ($property->geocode_status === 'manually_adjusted' ? 'warning' : ($property->geocode_status === 'failed' ? 'danger' : 'muted')) }}">
                            {{ str_replace('_', ' ', $property->geocode_status) }}
                        </span>
                        <span class="text-muted small">{{ $property->location_source }}</span>
                    </div>
                    <div id="map" style="height: 240px; border-radius: 4px; background: var(--cw-canvas, #eef1f5);"></div>
                    @if(auth()->user()->hasPermission('3.3'))
                        <form method="POST" action="{{ route('properties.retry-geocode', $property) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Re-run geocoding
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(auth()->user()->hasPermission('3.6'))
                <div class="card shadow-sm mb-3 reveal" style="--d: 160ms">
                    <div class="card-header mono">Assignments</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('property-assignments.store', $property) }}" class="row g-2">
                            @csrf
                            <div class="col-6">
                                <label for="assignable_type" class="form-label visually-hidden">Type</label>
                                <select name="assignable_type" id="assignable_type" class="form-select form-select-sm">
                                    <option value="user">Person</option>
                                    <option value="team">Team</option>
                                    <option value="branch">Branch</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="assignment_role" class="form-label visually-hidden">Role</label>
                                <select name="assignment_role" id="assignment_role" class="form-select form-select-sm">
                                    @foreach (['manager', 'supervisor', 'cleaner', 'team', 'branch', 'service_area'] as $role)
                                        <option value="{{ $role }}">{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="assignable_id" class="form-label visually-hidden">Assignable</label>
                                <input type="number" name="assignable_id" id="assignable_id" class="form-control form-control-sm" placeholder="Assignable ID" required min="1">
                            </div>
                            <div class="col-6">
                                <label for="start_date" class="form-label visually-hidden">Start</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label for="end_date" class="form-label visually-hidden">End</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 d-flex align-items-center gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary">
                                    <label class="form-check-label small" for="is_primary">Primary</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="reason" class="form-label visually-hidden">Reason</label>
                                <input type="text" name="reason" id="reason" class="form-control form-control-sm" placeholder="Reason (optional)">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="bi bi-plus me-1" aria-hidden="true"></i>Add assignment
                                </button>
                            </div>
                        </form>

                        <hr>
                        @forelse ($property->assignments as $assignment)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <div>
                                    <span class="fw-semibold small">{{ $assignment->assignable?->name ?? ('#'.$assignment->assignable_id) }}</span>
                                    <span class="status-badge status-muted ms-2">{{ $assignment->assignment_role }}</span>
                                    @if($assignment->is_primary)<span class="status-badge status-warning">primary</span>@endif
                                    <div class="text-muted small">
                                        {{ $assignment->start_date?->toDateString() ?? '—' }} → {{ $assignment->end_date?->toDateString() ?? '∞' }}
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('property-assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0" aria-label="Remove assignment">
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No assignments yet.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function ($) {
            var $lat = $('#latitude'), $lng = $('#longitude');
            var lat = parseFloat($lat.val()), lng = parseFloat($lng.val());
            if (!(isFinite(lat) && isFinite(lng))) { lat = -40.9; lng = 174.9; }
            var map = L.map('map').setView([lat, lng], isFinite($lat.val()) ? 16 : 5);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);
            if (isFinite($lat.val()) && isFinite($lng.val())) {
                var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    var p = marker.getLatLng();
                    $lat.val(p.lat.toFixed(7));
                    $lng.val(p.lng.toFixed(7));
                });
            }
        })(jQuery);
    </script>
@endpush
