@extends('layouts.app')

@section('title', 'System Settings & Office Location')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #settings-office-map {
        height: 280px;
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
            <span class="eyebrow">System · Settings</span>
            <h1 class="h3 mt-1 mb-0 fw-bold">Organization & Office Geofence Settings</h1>
            <p class="text-muted small mb-0">Configure primary office coordinates, geofence radius, and system defaults.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="reveal" style="--d: 80ms">
        @csrf

        <!-- Geofence & GPS Control Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-geo-alt text-primary me-2"></i>GPS & Geofencing Enforcement</span>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 mono">Disabled by Default</span>
            </div>
            <div class="card-body">
                @php
                    $geofenceEnforcedSetting = $settings->firstWhere('key', 'geofence_enforced')?->value ?? '0';
                @endphp
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="fw-bold text-dark mb-1">Enable Geofence Distance Validation</div>
                        <div class="text-muted small mb-0">
                            When <strong>disabled</strong> (default), cleaners punch in and punch out smoothly from any location while location coordinates are still recorded on the backend. When <strong>enabled</strong>, punch in is strictly gated within property radius boundaries.
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <select name="settings[system:geofence_enforced]" class="form-select form-select-sm fw-bold mono">
                            <option value="0" {{ $geofenceEnforcedSetting === '0' ? 'selected' : '' }}>Disabled (Default - Record GPS Only)</option>
                            <option value="1" {{ $geofenceEnforcedSetting === '1' ? 'selected' : '' }}>Enabled (Enforce Radius Gating)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Office Location Map Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span class="mono text-xs text-uppercase"><i class="bi bi-geo-alt-fill text-warning me-2"></i>Primary HQ Office Geofence Location</span>
                <button type="button" class="btn btn-xs btn-outline-light mono" id="btn-sys-my-location">
                    <i class="bi bi-crosshair me-1"></i>Pin My GPS Position
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark">Interactive Office Geofence Map</span>
                            <span class="mono text-xs text-muted">Click or drag pin to center main office location</span>
                        </div>
                        <div id="settings-office-map" class="mb-2"></div>
                    </div>

                    @php
                        $sysLat = $settings->firstWhere('key', 'office_latitude')?->value ?? config('gps.office_latitude', 27.7172);
                        $sysLng = $settings->firstWhere('key', 'office_longitude')?->value ?? config('gps.office_longitude', 85.3240);
                        $sysRad = $settings->firstWhere('key', 'office_radius_meters')?->value ?? config('gps.office_radius_meters', 100);
                    @endphp

                    <div class="col-md-4">
                        <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Default Office Latitude</label>
                        <input type="number" step="any" id="sys-office-lat" name="settings[system:office_latitude]" value="{{ $sysLat }}" class="form-control form-control-sm mono">
                        <div class="form-text">System fallback latitude for personnel attendance.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Default Office Longitude</label>
                        <input type="number" step="any" id="sys-office-lng" name="settings[system:office_longitude]" value="{{ $sysLng }}" class="form-control form-control-sm mono">
                        <div class="form-text">System fallback longitude for personnel attendance.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mono text-xs text-uppercase fw-bold mb-1">Default Radius (Meters)</label>
                        <input type="number" id="sys-office-rad" name="settings[system:office_radius_meters]" value="{{ $sysRad }}" min="10" max="10000" class="form-control form-control-sm mono">
                        <div class="form-text">Allowed check-in distance radius in meters.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Settings Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header mono"><i class="bi bi-building me-2"></i>Organization Profile</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($settings->where('scope', 'organization') as $setting)
                        <div class="col-md-6">
                            <label for="settings[organization:{{ $setting->key }}]" class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                            <input type="text" id="settings[organization:{{ $setting->key }}]" name="settings[organization:{{ $setting->key }}]"
                                   value="{{ $setting->value }}" class="form-control form-control-sm">
                            <div class="form-text">{{ $setting->description }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- General System Settings Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header mono"><i class="bi bi-sliders me-2"></i>System Parameters</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($settings->where('scope', 'system')->whereNotIn('key', ['office_latitude', 'office_longitude', 'office_radius_meters']) as $setting)
                        <div class="col-md-6">
                            <label for="settings[system:{{ $setting->key }}]" class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                            <input type="text" id="settings[system:{{ $setting->key }}]" name="settings[system:{{ $setting->key }}]"
                                   value="{{ $setting->value }}" class="form-control form-control-sm">
                            <div class="form-text">{{ $setting->description }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-4 fw-bold">
            <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save Organization & Map Settings
        </button>
    </form>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const initLat = parseFloat($('#sys-office-lat').val()) || 27.7172;
        const initLng = parseFloat($('#sys-office-lng').val()) || 85.3240;
        const initRad = parseInt($('#sys-office-rad').val()) || 100;

        const map = L.map('settings-office-map').setView([initLat, initLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
        let circle = L.circle([initLat, initLng], { radius: initRad, color: '#ff6b1a', fillColor: '#ff6b1a', fillOpacity: 0.2 }).addTo(map);

        function updateInputs(lat, lng) {
            $('#sys-office-lat').val(lat.toFixed(7));
            $('#sys-office-lng').val(lng.toFixed(7));
        }

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            circle.setLatLng(pos);
            updateInputs(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        $('#sys-office-lat, #sys-office-lng').on('change input', function () {
            const lat = parseFloat($('#sys-office-lat').val());
            const lng = parseFloat($('#sys-office-lng').val());
            if (!isNaN(lat) && !isNaN(lng)) {
                const pos = L.latLng(lat, lng);
                marker.setLatLng(pos);
                circle.setLatLng(pos);
                map.panTo(pos);
            }
        });

        $('#sys-office-rad').on('change input', function () {
            const r = parseInt($(this).val()) || 100;
            circle.setRadius(r);
        });

        $('#btn-sys-my-location').on('click', function () {
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
    });
</script>
@endpush
