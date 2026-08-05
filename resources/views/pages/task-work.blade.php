@extends('layouts.app')

@section('title', $task->title)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #punch-map { height: 260px; border-radius: 4px; background: var(--cw-canvas, #eef1f5); }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">My task · {{ $task->reference_number }}</span>
            <h2 class="h3 mt-1 mb-0">{{ $task->title }}</h2>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="status-badge status-{{ $task->status === 'approved' || $task->status === 'completed' ? 'active' : (in_array($task->status, ['in_progress', 'accepted']) ? 'warning' : (in_array($task->status, ['cancelled', 'rejected']) ? 'danger' : 'muted')) }}">
                {{ str_replace('_', ' ', $task->status) }}
            </span>
            <a href="{{ auth()->user()->hasPermission('4.9') ? route('tasks') : route('tasks.my') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>My tasks
            </a>
        </div>
    </div>

    <div id="work-alert" class="alert py-2 reveal" role="alert" style="display: none;"></div>
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3 reveal" style="--d: 80ms">
                <div class="card-header mono">Task</div>
                <ul class="list-unstyled small mb-0 p-3">
                    <li class="mb-1"><span class="text-muted">Location:</span> <strong>{{ $task->property_name_snapshot ?? 'One-off location' }}</strong></li>
                    <li class="mb-1"><span class="text-muted">Address:</span> {{ $task->address_snapshot ?? '—' }}
                        @include('partials.directions-button', ['task' => $task, 'class' => 'ms-2'])
                    </li>
                    <li class="mb-1"><span class="text-muted">When:</span> {{ $task->scheduled_start_at?->format('D j M H:i') }} → {{ $task->scheduled_end_at?->format('H:i') }}</li>
                    <li class="mb-1"><span class="text-muted">Priority:</span> <span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">{{ $task->priority }}</span></li>
                    <li class="mb-1"><span class="text-muted">Approval:</span> {{ $task->approval_required ? 'required' : 'not required' }}</li>
                    @if($task->description)<li class="mt-2 pt-2 border-top">{{ $task->description }}</li>@endif
                </ul>
            </div>

            <div class="card shadow-sm mb-3 reveal" style="--d: 120ms" id="start-work-card">
                <div class="card-header mono">Start work</div>
                <div class="card-body">
                    @if(in_array($task->status, ['assigned', 'accepted']))
                        <div id="start-controls">
                            <button type="button" class="btn btn-primary w-100 btn-lg" id="btn-checkin">
                                <i class="bi bi-play-fill me-1" aria-hidden="true"></i><span id="btn-checkin-label">Punch in & start</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" id="btn-permission" style="display: none;">
                                <i class="bi bi-geo me-1" aria-hidden="true"></i><span id="btn-permission-label">Allow location access</span>
                            </button>
                            <div class="text-muted small mt-2" id="gps-status"><i class="bi bi-geo me-1" aria-hidden="true"></i>Location verified against the property geofence on punch-in.</div>
                            <button type="button" class="btn btn-link btn-sm p-0 mt-2" data-bs-toggle="collapse" data-bs-target="#manual-location" aria-expanded="false" aria-controls="manual-location">
                                <i class="bi bi-pin-map me-1" aria-hidden="true"></i>Location denied or unavailable? Enter coordinates manually
                            </button>
                            <div class="collapse mt-2" id="manual-location">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="manual-lat" class="form-label visually-hidden">Latitude</label>
                                        <input type="number" step="any" id="manual-lat" class="form-control form-control-sm" placeholder="Latitude">
                                    </div>
                                    <div class="col-6">
                                        <label for="manual-lng" class="form-label visually-hidden">Longitude</label>
                                        <input type="number" step="any" id="manual-lng" class="form-control form-control-sm" placeholder="Longitude">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btn-checkin-manual">
                                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i>Punch in with these coordinates
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted small">
                            <i class="bi bi-check-circle text-success me-1" aria-hidden="true"></i>
                            Work already started
                            @if($task->started_at) at {{ $task->started_at->format('D j M H:i') }}@endif.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-3 reveal {{ $lastPunch ? '' : 'd-none' }}" style="--d: 140ms" id="punch-card">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span>Punch-in record</span>
                    <span class="status-badge status-{{ $lastPunch && $lastPunch['inside_geofence'] ? 'active' : 'danger' }}" id="punch-badge">{{ $lastPunch && $lastPunch['inside_geofence'] ? 'inside geofence' : 'outside geofence' }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted">Punched in at</div>
                            <div class="fw-semibold" id="punch-time">{{ $lastPunch ? \Illuminate\Support\Carbon::parse($lastPunch['punched_in_at'])->format('D, j M Y H:i:s') : '—' }}</div>
                            <div class="small text-muted mt-2">Distance / radius</div>
                            <div class="fw-semibold" id="punch-distance">
                                @if($lastPunch)
                                    {{ $lastPunch['distance_meters'] !== null ? round($lastPunch['distance_meters']).' m' : '—' }} / {{ $lastPunch['radius_meters'] ?? '—' }} m
                                @else
                                    —
                                @endif
                            </div>
                            <div class="small text-muted mt-2">Coordinates</div>
                            <div class="fw-semibold mono small" id="punch-coords">
                                @if($lastPunch && $lastPunch['latitude']){{ round($lastPunch['latitude'], 6) }}, {{ round($lastPunch['longitude'], 6) }}@else—@endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="punch-map"></div>
                            <div class="text-muted small mt-2" id="punch-reason">
                                @if($lastPunch)
                                    @if($lastPunch['inside_geofence'])
                                        <i class="bi bi-check-circle text-success me-1" aria-hidden="true"></i>You were inside the permitted radius when you punched in.
                                    @else
                                        <i class="bi bi-exclamation-triangle text-danger me-1" aria-hidden="true"></i>
                                        You were outside the permitted radius ({{ $lastPunch['distance_meters'] !== null ? round($lastPunch['distance_meters']).' m' : '—' }} > {{ $lastPunch['radius_meters'] ?? '—' }} m) when you punched in. Punch-in was still recorded for review.
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($task->subtasks->isNotEmpty())
            <div class="card shadow-sm mb-3 reveal" style="--d: 160ms">
                <div class="card-header mono">Sub tasks</div>
                <div class="card-body py-2" id="subtask-list">
                    @foreach ($task->subtasks as $subtask)
                        <div class="form-check py-1 border-bottom subtask-item" data-id="{{ $subtask->id }}">
                            <input class="form-check-input subtask-tick" type="checkbox" id="sub-{{ $subtask->id }}"
                                   @checked($subtask->completed_at) @disabled($task->status !== 'in_progress')>
                            <label class="form-check-label small {{ $subtask->completed_at ? 'text-decoration-line-through text-muted' : '' }}" for="sub-{{ $subtask->id }}">
                                {{ $subtask->title }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-8">
            @if($task->checklistSnapshot->isNotEmpty())
            <div class="card shadow-sm mb-3 reveal" style="--d: 100ms">
                <div class="card-header mono">Checklist</div>
                <div class="card-body">
                    @foreach ($task->checklistSnapshot->groupBy('section_name') as $section => $items)
                        <div class="fw-semibold small text-uppercase text-muted mb-1">{{ $section }}</div>
                        <ul class="list-unstyled ms-2 mb-3">
                            @foreach ($items as $item)
                                <li class="small">
                                    <i class="bi bi-{{ $item->item_type === 'photo' ? 'camera' : ($item->item_type === 'text' ? 'font' : ($item->item_type === 'numeric' ? '123' : 'check-circle')) }} me-1" aria-hidden="true"></i>
                                    {{ $item->item_label }}
                                    @if($item->required)<span class="text-danger">*</span>@endif
                                    <span class="status-badge status-muted ms-1">{{ str_replace('_', ' ', $item->item_type) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
            @endif

            @include('partials.evidence-upload', ['task' => $task])

            <div class="card shadow-sm mb-3 reveal {{ in_array($task->status, ['in_progress', 'completed', 'submitted_for_approval'], true) ? '' : 'd-none' }}" style="--d: 200ms" id="finish-card">
                <div class="card-header mono">Finish</div>
                <div class="card-body">
                    <label for="work-remarks" class="form-label">Completion remarks</label>
                    <textarea id="work-remarks" rows="2" class="form-control form-control-sm" placeholder="What was done, keys returned, anything to flag…"></textarea>
                    <button type="button" class="btn btn-success w-100 mt-2" id="btn-complete">
                        <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                        {{ $task->approval_required ? 'Complete & send for approval' : 'Complete task' }}
                    </button>
                    <div class="text-muted small mt-2" id="complete-status"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="outsideModal" tabindex="-1" aria-labelledby="outsideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outsideModalLabel"><i class="bi bi-geo-alt me-2" aria-hidden="true"></i>Punch-in outside geofence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2" id="outside-reason" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted">Punched in at</div>
                            <div class="fw-semibold" id="outside-time">—</div>
                            <div class="small text-muted mt-2">Distance / radius</div>
                            <div class="fw-semibold" id="outside-distance">—</div>
                            <div class="small text-muted mt-2">Your location</div>
                            <div class="fw-semibold mono small" id="outside-coords">—</div>
                        </div>
                        <div class="col-md-8">
                            <div id="outside-map" style="height: 280px; border-radius: 4px; background: var(--cw-canvas, #eef1f5);"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>@include('partials.webp-convert')</script>
    <script>
        (function ($) {
            var taskId = {{ $task->id }};
            var punchMap = null;
            var lastPunch = @json($lastPunch);

            function showAlert(type, msg) {
                var $a = $('#work-alert');
                $a.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + type).text(msg).show();
            }

            // Leaflet: property marker + radius circle + user location.
            function renderPunch(punch) {
                if (!punch || !punch.latitude || !punch.longitude || !window.L) return;

                $('#punch-card').removeClass('d-none');
                $('#punch-time').text(punch.punched_in_at ? new Date(punch.punched_in_at).toLocaleString() : '—');
                $('#punch-distance').text(
                    (punch.distance_meters !== null && punch.distance_meters !== undefined ? Math.round(punch.distance_meters) + ' m' : '—') + ' / ' + (punch.radius_meters || '—') + ' m'
                );
                $('#punch-coords').text(round6(punch.latitude) + ', ' + round6(punch.longitude));

                var inside = punch.inside_geofence === true;
                $('#punch-badge').attr('class', 'status-badge status-' + (inside ? 'active' : 'danger'))
                    .text(inside ? 'inside geofence' : 'outside geofence');

                var reason = '';
                if (inside) {
                    reason = '<i class="bi bi-check-circle text-success me-1" aria-hidden="true"></i>You were inside the permitted radius when you punched in.';
                } else {
                    reason = '<i class="bi bi-exclamation-triangle text-danger me-1" aria-hidden="true"></i>You were outside the permitted radius (' +
                        (punch.distance_meters !== null && punch.distance_meters !== undefined ? Math.round(punch.distance_meters) + ' m' : '—') + ' > ' + (punch.radius_meters || '—') + ' m) when you punched in. ' +
                        (punch.reason ? punch.reason + ' ' : '') + 'Punch-in was still recorded for review.';
                }
                $('#punch-reason').html(reason);

                if (!punchMap) {
                    punchMap = L.map('punch-map').setView([punch.latitude, punch.longitude], 16);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(punchMap);
                }

                punchMap.eachLayer(function (layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Circle) punchMap.removeLayer(layer);
                });

                var prop = [punch.property_latitude, punch.property_longitude];
                if (prop[0] !== null && prop[0] !== undefined) {
                    L.circle(prop, { radius: punch.radius_meters || 100, color: '#ff6b1a', weight: 2, fillColor: '#ff6b1a', fillOpacity: 0.12 }).addTo(punchMap);
                    L.marker(prop, { title: punch.property_name || 'Property' }).addTo(punchMap).bindPopup('<strong>' + (punch.property_name || 'Property') + '</strong>');
                }

                var color = inside ? '#198754' : '#dc3545';
                L.circleMarker([punch.latitude, punch.longitude], {
                    radius: 8, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1
                }).addTo(punchMap).bindPopup(inside ? 'You were here — inside geofence' : 'You were here — outside geofence');

                punchMap.invalidateSize();

                if (prop[0] !== null && prop[0] !== undefined) {
                    punchMap.fitBounds(L.latLngBounds([punch.latitude, punch.longitude], prop).pad(0.4));
                }
            }

            function round6(v) {
                return v === null || v === undefined ? '—' : Number(v).toFixed(6);
            }

            // Outside-geofence popup: modal + Leaflet map showing why punch-in failed.
            var outsideMap = null;
            function showOutsidePopup(punch) {
                if (!punch || punch.inside_geofence === true || !window.L) return;

                $('#outside-time').text(punch.punched_in_at ? new Date(punch.punched_in_at).toLocaleString() : '—');
                $('#outside-distance').text(
                    (punch.distance_meters !== null && punch.distance_meters !== undefined ? Math.round(punch.distance_meters) + ' m' : '—') + ' / ' + (punch.radius_meters || '—') + ' m'
                );
                $('#outside-coords').text(round6(punch.latitude) + ', ' + round6(punch.longitude));
                $('#outside-reason').html(
                    '<i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>' +
                    (punch.reason || 'Outside the permitted check-in radius.') +
                    ' The punch-in was still recorded for review.'
                );

                if (!outsideMap) {
                    outsideMap = L.map('outside-map').setView([punch.latitude, punch.longitude], 15);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(outsideMap);
                }

                outsideMap.eachLayer(function (layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Circle) outsideMap.removeLayer(layer);
                });

                var prop = [punch.property_latitude, punch.property_longitude];
                if (prop[0] !== null && prop[0] !== undefined) {
                    L.circle(prop, { radius: punch.radius_meters || 100, color: '#ff6b1a', weight: 2, fillColor: '#ff6b1a', fillOpacity: 0.12 }).addTo(outsideMap);
                    L.marker(prop).addTo(outsideMap).bindPopup('<strong>' + (punch.property_name || 'Property') + '</strong>');
                }
                L.circleMarker([punch.latitude, punch.longitude], {
                    radius: 8, color: '#fff', weight: 2, fillColor: '#dc3545', fillOpacity: 1
                }).addTo(outsideMap).bindPopup('You were here — outside the geofence');

                if (prop[0] !== null && prop[0] !== undefined) {
                    outsideMap.fitBounds(L.latLngBounds([punch.latitude, punch.longitude], prop).pad(0.4));
                }

                var modal = new bootstrap.Modal(document.getElementById('outsideModal'));
                modal.show();
                outsideMap.invalidateSize();
            }

            $('#outsideModal').on('shown.bs.modal', function () {
                if (outsideMap) outsideMap.invalidateSize();
            });

            // Punch in: browser geolocation → geo-validated check-in.
            function doCheckIn(lat, lng, accuracy) {
                var $btn = $('#btn-checkin');
                $btn.prop('disabled', true);
                $('#gps-status').html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Verifying location…');

                axios.post('{{ route('tasks.work-checkin', $task) }}', {
                    latitude: lat,
                    longitude: lng,
                    gps_accuracy_meters: accuracy || null
                }).then(function (res) {
                    $('#gps-status').html('<i class="bi bi-geo-fill me-1" aria-hidden="true"></i>' + res.data.message);
                    showAlert('success', res.data.message);
                    renderPunch(res.data.punch);
                    if (res.data.punch && res.data.punch.inside_geofence === true) {
                        // Successful punch: hide the whole start card, show finish + subtasks.
                        $('#start-work-card').hide();
                        updateStatus(res.data.task_status);
                    } else {
                        // Unsuccessful (outside geofence): keep the button, no finish.
                        $('#finish-card').addClass('d-none');
                        $('.subtask-tick').prop('disabled', true);
                    }
                    if (res.data.punch && res.data.punch.inside_geofence === false) {
                        showOutsidePopup(res.data.punch);
                    }
                }).catch(function (err) {
                    var msg = err.response?.data?.message || 'Punch-in failed.';
                    $('#gps-status').html('<i class="bi bi-geo-alt me-1" aria-hidden="true"></i>' + msg);
                    showAlert(err.response?.status === 403 ? 'warning' : 'danger', msg);
                    // Unsuccessful punch: never show finish or subtask ticks.
                    $('#finish-card').addClass('d-none');
                    $('.subtask-tick').prop('disabled', true);
                    // 403 (override/reject policy) still carries the recorded punch data.
                    if (err.response?.data?.punch) {
                        renderPunch(err.response.data.punch);
                        if (err.response.data.punch.inside_geofence === false) {
                            showOutsidePopup(err.response.data.punch);
                        }
                    }
                }).finally(function () {
                    $btn.prop('disabled', false);
                });
            }

            function askLocation() {
                return new Promise(function (resolve, reject) {
                    navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 10000 });
                });
            }

            // Check permission state on load; show an explicit request button when needed.
            function checkGeoState() {
                if (!window.isSecureContext) {
                    $('#gps-status').text('Location needs HTTPS (or localhost) — use manual coordinates below.');
                    $('#manual-location').collapse('show');
                    return;
                }
                if (!navigator.geolocation) {
                    $('#gps-status').text('Geolocation not supported in this browser — use manual coordinates below.');
                    $('#manual-location').collapse('show');
                    return;
                }
                if (navigator.permissions && navigator.permissions.query) {
                    navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
                        if (status.state === 'granted') {
                            $('#btn-permission').hide();
                        } else if (status.state === 'prompt') {
                            $('#btn-permission').show();
                            $('#btn-permission-label').text('Allow location access (browser will ask)');
                        } else {
                            $('#btn-permission').show();
                            $('#btn-permission-label').text('Location is blocked — tap to request again');
                        }
                    }).catch(function () {
                        $('#btn-permission').show();
                        $('#btn-permission-label').text('Allow location access');
                    });
                } else {
                    $('#btn-permission').show();
                    $('#btn-permission-label').text('Allow location access');
                }
            }

            // Explicit permission request — triggers the browser prompt.
            $('#btn-permission').on('click', function () {
                var $btn = $(this);
                $btn.prop('disabled', true);
                $('#gps-status').html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Requesting location permission…');
                askLocation().then(function (pos) {
                    $('#btn-permission').hide();
                    $('#gps-status').text('Location allowed — punch in to start.');
                    showAlert('success', 'Location permission granted. Punch in to start.');
                    doCheckIn(pos.coords.latitude, pos.coords.longitude, Math.round(pos.coords.accuracy || 0));
                }).catch(function (err) {
                    showGeoError(err);
                    if (err && err.code === 1) {
                        $('#btn-permission-label').text('Still blocked — enable location for this site in browser settings');
                    }
                }).finally(function () {
                    $btn.prop('disabled', false);
                });
            });

            $('#btn-checkin').on('click', function () {
                if (!navigator.geolocation) { showGeoError(2); return; }
                askLocation()
                    .then(function (pos) {
                        doCheckIn(pos.coords.latitude, pos.coords.longitude, Math.round(pos.coords.accuracy || 0));
                    })
                    .catch(showGeoError);
            });

            // Delegated + duplicate-proof: manual punch-in always bound.
            $(document).on('click', '#btn-checkin-manual', function () {
                var lat = parseFloat($('#manual-lat').val());
                var lng = parseFloat($('#manual-lng').val());
                if (!isFinite(lat) || !isFinite(lng)) {
                    $('#gps-status').text('Enter both latitude and longitude (e.g. -36.8484 and 174.7633).');
                    return;
                }
                doCheckIn(lat, lng, null);
            });

            function showGeoError(err) {
                var msg;
                if (err && err.code === 1) {
                    msg = 'Location access denied — enable location for this site in your browser settings (or press "Allow location access"), or use manual coordinates below.';
                } else if (err && err.code === 2) {
                    msg = 'Location unavailable (no GPS signal) — use manual coordinates below.';
                } else if (err && err.code === 3) {
                    msg = 'Location request timed out — try again or use manual coordinates below.';
                } else {
                    msg = 'Location unavailable — use manual coordinates below.';
                }
                $('#gps-status').text(msg);
                showAlert('warning', msg);
                $('#btn-permission').show();
                $('#btn-permission-label').text('Allow location access (browser will ask)');
                $('#manual-location').collapse('show');
            }

            // Sub task tick (ajax).
            $(document).on('change', '.subtask-tick', function () {
                var $box = $(this), id = $box.closest('.subtask-item').data('id');
                axios.post('{{ route('tasks.subtasks.toggle', [$task, '__ID__']) }}'.replace('__ID__', id))
                    .then(function () {
                        $box.closest('.subtask-item').find('label').toggleClass('text-decoration-line-through text-muted');
                    })
                    .catch(function () { $box.prop('checked', !$box.prop('checked')); });
            });

            @include('partials.evidence-upload-js', ['task' => $task])

            // Complete (+ approval submit when required).
            $(document).on('click', '#btn-complete', function () {
                var $btn = $(this);
                if (!confirm('Finish this task?')) return;
                $btn.prop('disabled', true);
                $('#complete-status').html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Sending…');

                axios.post('{{ route('tasks.complete', $task) }}', {
                    remarks: $('#work-remarks').val() || ''
                }).then(function (res) {
                    $('#complete-status').text(res.data.message);
                    showAlert('success', res.data.message);
                    updateStatus(res.data.task_status);
                    $('.subtask-tick').prop('disabled', true);
                }).catch(function (err) {
                    var data = err.response?.data || {};
                    $('#complete-status').text(data.message || 'Completion failed.');
                    if (data.missing) {
                        showAlert('danger', data.message + ' — ' + data.missing.join('; '));
                    } else {
                        showAlert('danger', data.message || 'Completion failed.');
                    }
                }).finally(function () {
                    $btn.prop('disabled', false);
                });
            });

            function updateStatus(status) {
                $('.status-badge').first().text(String(status || '').replace(/_/g, ' '));
                // Finish card + subtask ticks appear only after a successful punch-in.
                if (status === 'in_progress') {
                    $('#finish-card').removeClass('d-none');
                    $('.subtask-tick').prop('disabled', false);
                }
            }

            checkGeoState();

            // Show the last punch (time + map) when the page loads after a punch-in.
            if (lastPunch) {
                renderPunch(lastPunch);
            }
        })(jQuery);
    </script>
@endpush
