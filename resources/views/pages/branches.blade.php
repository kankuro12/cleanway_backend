@extends('layouts.app')

@section('title', 'Branch Offices & Geofencing')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #branch-map, .edit-branch-map {
        height: 260px;
        width: 100%;
        border-radius: var(--cw-radius-md, 4px);
        border: 1px solid var(--cw-border, #d8dfe8);
        z-index: 1;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People · Offices & Geofencing</span>
            <h1 class="h3 mt-1 mb-0 fw-bold">Branch Offices & Map Settings</h1>
            <p class="text-muted small mb-0">Manage physical branch office locations and attendance geofence radii.</p>
        </div>
        <a href="{{ route('personnel') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to personnel
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Add Branch Form with Interactive Map Picker -->
        <div class="col-lg-5">
            <div class="card shadow-sm reveal" style="--d: 80ms">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="mono text-xs text-uppercase"><i class="bi bi-plus-lg me-1 text-warning"></i>Add Branch Office</span>
                    <button type="button" class="btn btn-xs btn-outline-light mono" id="btn-use-my-location">
                        <i class="bi bi-crosshair me-1"></i>Use My Location
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('branches.store') }}" id="create-branch-form">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold small">Branch Name *</label>
                            <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="e.g. Downtown HQ Office" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold small">Address</label>
                            <input type="text" id="address" name="address" class="form-control form-control-sm" placeholder="Street address or city...">
                        </div>

                        <!-- Interactive Leaflet Map -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small mb-0"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Office Map Location</label>
                                <span class="mono text-xs text-muted">Click or drag pin to set position</span>
                            </div>
                            <div id="branch-map"></div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6 col-sm-4">
                                <label for="latitude" class="form-label mono text-xs text-uppercase fw-bold mb-1">Latitude</label>
                                <input type="number" step="any" id="latitude" name="latitude" value="{{ config('gps.office_latitude') }}" class="form-control form-control-sm mono">
                            </div>
                            <div class="col-6 col-sm-4">
                                <label for="longitude" class="form-label mono text-xs text-uppercase fw-bold mb-1">Longitude</label>
                                <input type="number" step="any" id="longitude" name="longitude" value="{{ config('gps.office_longitude') }}" class="form-control form-control-sm mono">
                            </div>
                            <div class="col-12 col-sm-4">
                                <label for="geofence_radius_meters" class="form-label mono text-xs text-uppercase fw-bold mb-1">Radius (m)</label>
                                <input type="number" id="geofence_radius_meters" name="geofence_radius_meters" value="100" min="10" max="10000" class="form-control form-control-sm mono">
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Create Branch Office
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registered Branches Register Table -->
        <div class="col-lg-7">
            <div class="card shadow-sm reveal" style="--d: 140ms">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h2 class="h6 mb-0 mono text-uppercase fw-bold"><i class="bi bi-building me-2 text-primary"></i>Branch Offices</h2>
                    <span class="badge bg-secondary rounded-pill">{{ count($branches) }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light mono text-xs text-uppercase">
                            <tr>
                                <th>Name & Address</th>
                                <th>Geofence Location</th>
                                <th>Staff</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $branch)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $branch->name }}</div>
                                        <div class="small text-muted">{{ $branch->address ?? 'No address provided' }}</div>
                                    </td>
                                    <td>
                                        @if($branch->latitude && $branch->longitude)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle mono text-xs d-block mb-1">
                                                <i class="bi bi-geo-alt-fill me-1"></i>{{ number_format($branch->latitude, 4) }}, {{ number_format($branch->longitude, 4) }}
                                            </span>
                                            <span class="mono text-xs text-muted">Radius: {{ $branch->geofence_radius_meters ?? 100 }}m</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary mono text-xs">Default System Fallback</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle mono">{{ $branch->users_count }} users</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $branch->active ? 'active' : 'muted' }}">{{ $branch->active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBranchModal{{ $branch->id }}">
                                                <i class="bi bi-pencil me-1"></i>Edit
                                            </button>

                                            <form method="POST" action="{{ route('branches.update', $branch) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $branch->name }}">
                                                <input type="hidden" name="address" value="{{ $branch->address }}">
                                                <input type="hidden" name="latitude" value="{{ $branch->latitude }}">
                                                <input type="hidden" name="longitude" value="{{ $branch->longitude }}">
                                                <input type="hidden" name="geofence_radius_meters" value="{{ $branch->geofence_radius_meters }}">
                                                <input type="hidden" name="active" value="{{ $branch->active ? 0 : 1 }}">
                                                <button class="btn btn-sm btn-outline-secondary">{{ $branch->active ? 'Deactivate' : 'Activate' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Branch Modal with Map -->
                                <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1" aria-labelledby="editBranchModalLabel{{ $branch->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title h6 mono text-uppercase mb-0" id="editBranchModalLabel{{ $branch->id }}">Edit Branch: {{ $branch->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('branches.update', $branch) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small">Branch Name *</label>
                                                            <input type="text" name="name" value="{{ $branch->name }}" class="form-control form-control-sm" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold small">Address</label>
                                                            <input type="text" name="address" value="{{ $branch->address }}" class="form-control form-control-sm">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Office Map Location</label>
                                                            <div id="edit-map-{{ $branch->id }}" class="edit-branch-map mb-2"></div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Latitude</label>
                                                            <input type="number" step="any" id="edit-lat-{{ $branch->id }}" name="latitude" value="{{ $branch->latitude ?? config('gps.office_latitude') }}" class="form-control form-control-sm mono">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Longitude</label>
                                                            <input type="number" step="any" id="edit-lng-{{ $branch->id }}" name="longitude" value="{{ $branch->longitude ?? config('gps.office_longitude') }}" class="form-control form-control-sm mono">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Radius (meters)</label>
                                                            <input type="number" id="edit-rad-{{ $branch->id }}" name="geofence_radius_meters" value="{{ $branch->geofence_radius_meters ?? 100 }}" min="10" max="10000" class="form-control form-control-sm mono">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Branch Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-building fs-1 d-block mb-2 text-secondary"></i>
                                        No branch offices registered yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = parseFloat($('#latitude').val()) || 27.7172;
        const defaultLng = parseFloat($('#longitude').val()) || 85.3240;
        const defaultRadius = parseInt($('#geofence_radius_meters').val()) || 100;

        // Initialize Create Branch Leaflet Map
        const map = L.map('branch-map').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        let circle = L.circle([defaultLat, defaultLng], { radius: defaultRadius, color: '#ff6b1a', fillColor: '#ff6b1a', fillOpacity: 0.2 }).addTo(map);

        function updateInputs(lat, lng) {
            $('#latitude').val(lat.toFixed(7));
            $('#longitude').val(lng.toFixed(7));
        }

        function updateCircle() {
            const rad = parseInt($('#geofence_radius_meters').val()) || 100;
            circle.setRadius(rad);
        }

        marker.on('dragend', function (e) {
            const pos = marker.getLatLng();
            circle.setLatLng(pos);
            updateInputs(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        $('#latitude, #longitude').on('change input', function () {
            const lat = parseFloat($('#latitude').val());
            const lng = parseFloat($('#longitude').val());
            if (!isNaN(lat) && !isNaN(lng)) {
                const pos = L.latLng(lat, lng);
                marker.setLatLng(pos);
                circle.setLatLng(pos);
                map.panTo(pos);
            }
        });

        $('#geofence_radius_meters').on('change input', updateCircle);

        $('#btn-use-my-location').on('click', function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    const pos = L.latLng(position.coords.latitude, position.coords.longitude);
                    marker.setLatLng(pos);
                    circle.setLatLng(pos);
                    map.setView(pos, 16);
                    updateInputs(pos.lat, pos.lng);
                });
            }
        });

        // Initialize Edit Modals Maps dynamically
        @foreach($branches as $b)
            (function() {
                const modalEl = document.getElementById('editBranchModal{{ $b->id }}');
                let editMap = null;
                let editMarker = null;
                let editCircle = null;

                modalEl.addEventListener('shown.bs.modal', function () {
                    const eLat = parseFloat($('#edit-lat-{{ $b->id }}').val()) || 27.7172;
                    const eLng = parseFloat($('#edit-lng-{{ $b->id }}').val()) || 85.3240;
                    const eRad = parseInt($('#edit-rad-{{ $b->id }}').val()) || 100;

                    if (!editMap) {
                        editMap = L.map('edit-map-{{ $b->id }}').setView([eLat, eLng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(editMap);

                        editMarker = L.marker([eLat, eLng], { draggable: true }).addTo(editMap);
                        editCircle = L.circle([eLat, eLng], { radius: eRad, color: '#ff6b1a', fillColor: '#ff6b1a', fillOpacity: 0.2 }).addTo(editMap);

                        editMarker.on('dragend', function (e) {
                            const pos = editMarker.getLatLng();
                            editCircle.setLatLng(pos);
                            $('#edit-lat-{{ $b->id }}').val(pos.lat.toFixed(7));
                            $('#edit-lng-{{ $b->id }}').val(pos.lng.toFixed(7));
                        });

                        editMap.on('click', function (e) {
                            editMarker.setLatLng(e.latlng);
                            editCircle.setLatLng(e.latlng);
                            $('#edit-lat-{{ $b->id }}').val(e.latlng.lat.toFixed(7));
                            $('#edit-lng-{{ $b->id }}').val(e.latlng.lng.toFixed(7));
                        });

                        $('#edit-rad-{{ $b->id }}').on('change input', function () {
                            const r = parseInt($(this).val()) || 100;
                            editCircle.setRadius(r);
                        });
                    } else {
                        editMap.invalidateSize();
                    }
                });
            })();
        @endforeach
    });
</script>
@endpush
