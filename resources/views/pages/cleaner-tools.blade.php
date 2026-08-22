@extends('layouts.app')

@section('title', 'Cleaner & Staff Tools')

@section('content')
<div class="container-fluid p-0">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <span class="eyebrow">Field · Attendance Console</span>
        <h1 class="h2 text-strong mb-1">Personnel Office Punch Console</h1>
        <p class="text-muted small mb-0">Task-free office punch-in and punch-out geofenced to your office branch location.</p>
    </div>

    <div class="row g-4">
        <!-- Punch Console Card -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden">
                <!-- Desktop Only Card Header -->
                <div class="card-header bg-white border-bottom d-none d-md-flex justify-content-between align-items-center py-3 px-4">
                    <span class="mono text-xs text-uppercase letter-spacing-1 fw-bold text-dark">
                        <i class="bi bi-building me-2 text-primary"></i>ATTENDANCE PUNCH CONSOLE
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mono text-xs" id="gps-status-badge">
                        <i class="bi bi-geo-alt me-1"></i>GPS ACTIVE
                    </span>
                </div>
                <div class="card-body p-3 p-md-4 text-center">
                    <!-- Desktop Only Branch Info Banner -->
                    <div class="p-3 bg-light rounded-3 mb-4 text-start border d-none d-md-block">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-dark small"><i class="bi bi-geo-fill me-1 text-primary"></i>Assigned Branch: {{ $branch?->name ?? 'Head Office' }}</span>
                            <span class="mono extra-small badge bg-secondary-subtle text-secondary">Radius: {{ $officeRadius }}m</span>
                        </div>
                    </div>

                    <!-- Current Status Indicator -->
                    <div class="mb-4">
                        <span class="mono text-xs text-uppercase text-muted d-block mb-1">Current Punch Status</span>
                        @php
                            $lastType = $lastEvent?->event_type;
                            $isPunchedIn = in_array($lastType, ['clock_in', 'break_end']);
                            $isOnBreak = $lastType === 'break_start';
                        @endphp

                        @if($isPunchedIn)
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-success text-white rounded-pill shadow-sm">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                <span class="mono fw-bold text-uppercase text-xs">PUNCHED IN AT OFFICE</span>
                            </div>
                        @elseif($isOnBreak)
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-warning text-dark rounded-pill shadow-sm">
                                <i class="bi bi-cup-hot-fill"></i>
                                <span class="mono fw-bold text-uppercase text-xs">CURRENTLY ON BREAK</span>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                <span class="dot bg-secondary"></span>
                                <span class="mono fw-bold text-uppercase text-xs">PUNCHED OUT</span>
                            </div>
                        @endif
                    </div>

                    <!-- Punch Action Form -->
                    <form method="POST" action="{{ route('attendance.office-punch') }}" id="office-punch-form">
                        @csrf
                        <input type="hidden" name="event_type" id="punch-event-type" value="clock_in">
                        <input type="hidden" name="latitude" id="punch-latitude">
                        <input type="hidden" name="longitude" id="punch-longitude">
                        <input type="hidden" name="gps_accuracy_meters" id="punch-accuracy">

                        <div class="mb-3">
                            <input type="text" name="remarks" class="form-control rounded-pill px-3 py-2 text-center" placeholder="Optional remarks or work note...">
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            @if(! $isPunchedIn && ! $isOnBreak)
                                <button type="button" onclick="submitOfficePunch('clock_in')" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-sm rounded-pill flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-box-arrow-in-right fs-4"></i>Punch In to Office
                                </button>
                            @else
                                @if($isPunchedIn)
                                    <button type="button" onclick="submitOfficePunch('break_start')" class="btn btn-outline-warning btn-lg px-3 py-3 fw-bold rounded-pill flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-pause-circle me-1"></i>Start Break
                                    </button>
                                @elseif($isOnBreak)
                                    <button type="button" onclick="submitOfficePunch('break_end')" class="btn btn-info btn-lg px-3 py-3 text-white fw-bold rounded-pill flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-play-circle me-1"></i>End Break
                                    </button>
                                @endif
                                <button type="button" onclick="submitOfficePunch('clock_out')" class="btn btn-danger btn-lg px-4 py-3 fw-bold shadow-sm rounded-pill flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-box-arrow-right fs-4"></i>Punch Out
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Access & Recent Events Side Column -->
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 mb-0 mono text-uppercase fw-bold"><i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions</h2>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('tasks.my') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-clipboard-check text-primary me-2"></i>My Assigned Tasks</div>
                            <small class="text-muted">View tasks assigned to you for execution</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('reports.shifts') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i>Shift Report</div>
                            <small class="text-muted">Review attendance summary and worked hours</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('incidents.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Raise Incident</div>
                            <small class="text-muted">Report property access or safety issues</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const OFFICE_LAT = {{ (float)$officeLat }};
    const OFFICE_LNG = {{ (float)$officeLng }};
    const GEOFENCE_RADIUS = {{ (int)$officeRadius }};
    const OFFICE_NAME = "{{ addslashes($branch?->name ?? 'Central Office') }}";

    let toolsMap = null;
    let toolsUserMarker = null;
    let toolsOfficeMarker = null;
    let toolsCircle = null;
    let toolsPolyline = null;

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function formatDistanceText(meters) {
        if (meters >= 1000) {
            return (meters / 1000).toFixed(2) + ' km (' + meters.toLocaleString() + ' m)';
        }
        return meters.toLocaleString() + ' m';
    }

    function initToolsLiveMap(userLat, userLng) {
        // Maps disabled on cleaner side to conserve data
        return;
    }

    function acquireGpsLocation() {
        const GEOFENCE_ENFORCED = {{ (config('gps.geofence_enforced', false) || config('app.geofencing', false)) ? 'true' : 'false' }};

        if (!navigator.geolocation) {
            $('#gps-status-badge').removeClass('bg-warning').addClass('bg-secondary text-white').html('<i class="bi bi-geo me-1"></i>GPS Ready');
            $('#live-distance-text').text('Geolocation not supported by browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = Math.round(position.coords.accuracy);

                $('#punch-latitude').val(lat);
                $('#punch-longitude').val(lng);
                $('#punch-accuracy').val(accuracy);

                const distance = calculateDistance(lat, lng, OFFICE_LAT, OFFICE_LNG);
                const formattedDist = formatDistanceText(distance);

                if (!GEOFENCE_ENFORCED) {
                    $('#gps-status-badge').removeClass('bg-warning bg-danger').addClass('bg-success text-white')
                        .html('<i class="bi bi-check-circle-fill me-1"></i>GPS Active');
                    $('#live-distance-text').removeClass('text-danger').addClass('text-success fw-bold')
                        .text('GPS Recorded (' + formattedDist + ' from office)');
                } else {
                    const isInside = distance <= GEOFENCE_RADIUS;
                    if (isInside) {
                        $('#gps-status-badge').removeClass('bg-warning bg-danger').addClass('bg-success text-white')
                            .html('<i class="bi bi-check-circle-fill me-1"></i>Inside Office (' + formattedDist + ')');
                        $('#live-distance-text').removeClass('text-danger').addClass('text-success fw-bold')
                            .text(formattedDist + ' from office (Inside Geofence)');
                    } else {
                        $('#gps-status-badge').removeClass('bg-warning bg-success').addClass('bg-danger text-white')
                            .html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Outside Geofence (' + formattedDist + ')');
                        $('#live-distance-text').removeClass('text-success').addClass('text-danger fw-bold')
                            .text(formattedDist + ' from office (Outside Radius: ' + GEOFENCE_RADIUS + 'm)');
                    }
                }
            },
            function(error) {
                $('#gps-status-badge').removeClass('bg-warning').addClass('bg-secondary text-white')
                    .html('<i class="bi bi-geo me-1"></i>GPS Active');
                $('#live-distance-text').text('GPS location data recorded on punch.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function submitOfficePunch(type) {
        $('#punch-event-type').val(type);
        $('#office-punch-form').submit();
    }

    $(document).ready(function() {
        acquireGpsLocation();
        setInterval(acquireGpsLocation, 15000);
    });
</script>
@endpush
