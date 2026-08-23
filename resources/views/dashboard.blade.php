@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* Attendance Bar Redesign Tokens & Styles */
    :root {
        --att-ink: #14181F;
        --att-paper: #F7F8FA;
        --att-line: #E4E7EC;
        --att-muted: #8A8F98;
        --att-live-green: #1F9D63;
        --att-amber: #E0A100;
        --att-signal-coral: #E1553F;
    }

    .dash-sticky-tabs {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 56px;
        z-index: 1010;
        background: var(--cw-canvas, #f8fafc);
        padding: 0.25rem 0 0;
    }

    @media (max-width: 575.98px) {
        .dash-sticky-tabs {
            top: 0 !important;
            padding: 0.15rem 0 0;
        }
    }

    .attendance-bar-card {
        background: var(--att-paper, #F7F8FA) !important;
        border: 1px solid var(--att-line, #E4E7EC) !important;
        border-radius: 12px;
        transition: border-color 0.2s ease;
    }

    .attendance-bar-inner {
        min-height: 52px;
    }

    .attendance-office-icon {
        font-size: 16px;
        color: var(--att-muted, #8A8F98);
        line-height: 1;
    }

    .attendance-office-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--att-ink, #14181F);
        line-height: 1.2;
        white-space: nowrap;
    }

    .attendance-status-line {
        font-size: 12px;
        line-height: 1.2;
    }

    .attendance-status-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .attendance-status-label.state-in {
        color: var(--att-live-green, #1F9D63);
    }

    .attendance-status-label.state-break {
        color: var(--att-amber, #E0A100);
    }

    .attendance-status-label.state-out {
        color: var(--att-muted, #8A8F98);
    }

    .attendance-timer-divider {
        color: var(--att-muted, #8A8F98);
        user-select: none;
        margin: 0 1px;
    }

    .attendance-live-timer {
        font-family: var(--cw-font-mono, 'IBM Plex Mono', monospace);
        font-size: 12px;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
        color: var(--att-muted, #8A8F98);
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    /* Progress Ring & Dot */
    .attendance-ring-wrap {
        width: 16px;
        height: 16px;
        position: relative;
    }

    .attendance-ring-svg {
        width: 16px;
        height: 16px;
        display: block;
    }

    .attendance-ring-track {
        stroke: var(--att-line, #E4E7EC);
    }

    .attendance-ring-fill {
        transition: stroke-dashoffset 0.4s ease, stroke 0.25s ease;
    }

    .attendance-ring-fill.state-in {
        stroke: var(--att-live-green, #1F9D63);
    }

    .attendance-ring-fill.state-break {
        stroke: var(--att-amber, #E0A100);
    }

    .attendance-ring-fill.state-out {
        stroke: var(--att-line, #E4E7EC);
    }

    .attendance-ring-fill.attendance-ring-overtime {
        stroke: var(--att-signal-coral, #E1553F) !important;
        animation: att-overtime-pulse 1.5s infinite ease-in-out;
    }

    @keyframes att-overtime-pulse {
        0%, 100% { stroke: var(--att-signal-coral, #E1553F); opacity: 1; }
        50% { stroke: var(--att-signal-coral, #E1553F); opacity: 0.4; }
    }

    .attendance-status-dot {
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        top: 5px;
        left: 5px;
        transition: background-color 0.2s ease;
    }

    .attendance-status-dot.state-in {
        background-color: var(--att-live-green, #1F9D63);
        animation: att-breathing 2s infinite ease-in-out;
    }

    .attendance-status-dot.state-break {
        background-color: var(--att-amber, #E0A100);
    }

    .attendance-status-dot.state-out {
        background-color: var(--att-muted, #8A8F98);
    }

    @keyframes att-breathing {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1.0; }
    }

    /* Vertical Divider */
    .attendance-divider {
        width: 1px;
        height: 24px;
        background: var(--att-line, #E4E7EC);
        margin: 0 12px 0 8px;
    }

    /* Remarks Field — Ghost at rest */
    .attendance-remarks-wrap {
        position: relative;
    }

    .attendance-remarks-input {
        background: transparent !important;
        border: 1px solid transparent !important;
        height: 36px;
        min-height: 36px;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        color: var(--att-ink, #14181F);
        width: 160px;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .attendance-remarks-input::placeholder {
        color: var(--att-muted, #8A8F98);
        font-size: 13px;
    }

    .attendance-remarks-input:hover {
        background: #ffffff !important;
        border-color: var(--att-line, #E4E7EC) !important;
    }

    .attendance-remarks-input:focus {
        background: #ffffff !important;
        border-color: var(--att-line, #E4E7EC) !important;
        outline: none;
        box-shadow: 0 0 0 2px rgba(20, 24, 31, 0.08) !important;
    }

    /* Actions Cluster */
    .attendance-actions-group {
        gap: 8px;
    }

    .attendance-btn {
        height: 36px;
        min-height: 36px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        cursor: pointer;
        transition: transform 120ms ease, background 0.15s ease, border-color 0.15s ease;
        touch-action: manipulation;
    }

    .attendance-btn:active {
        transform: scale(0.97);
    }

    .attendance-btn-primary {
        background: var(--att-ink, #14181F) !important;
        color: #ffffff !important;
        border: 1px solid var(--att-ink, #14181F) !important;
    }

    .attendance-btn-primary:hover {
        background: #232a35 !important;
        border-color: #232a35 !important;
        color: #ffffff !important;
    }

    .attendance-btn-outline {
        background: transparent !important;
        border: 1px solid var(--att-line, #E4E7EC) !important;
        color: var(--att-ink, #14181F) !important;
    }

    .attendance-btn-outline:hover {
        background: #ffffff !important;
        border-color: var(--att-muted, #8A8F98) !important;
        color: var(--att-ink, #14181F) !important;
    }

    .attendance-btn-resume {
        background: transparent !important;
        border: 1px solid var(--att-amber, #E0A100) !important;
        color: var(--att-amber, #E0A100) !important;
    }

    .attendance-btn-resume:hover {
        background: rgba(224, 161, 0, 0.08) !important;
        border-color: var(--att-amber, #E0A100) !important;
        color: var(--att-amber, #E0A100) !important;
    }

    /* Responsive under 480px — Two rows */
    @media (max-width: 480px) {
        .attendance-bar-inner {
            flex-direction: column;
            align-items: stretch !important;
            padding: 0.65rem 0.75rem !important;
            gap: 8px !important;
        }
        .attendance-left-cluster {
            width: 100%;
            justify-content: space-between;
        }
        .attendance-right-cluster {
            width: 100%;
            margin-left: 0 !important;
            justify-content: space-between;
            gap: 8px !important;
        }
        .attendance-remarks-wrap {
            flex: 1 1 auto;
            min-width: 100px;
        }
        .attendance-remarks-input {
            width: 100%;
            height: 40px !important;
            min-height: 40px !important;
        }
        .attendance-actions-group {
            flex-shrink: 0;
        }
        .attendance-btn {
            height: 40px !important;
            min-height: 40px !important;
            padding: 0 14px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .attendance-status-dot.state-in {
            animation: none;
            opacity: 1;
        }
        .attendance-ring-fill {
            transition: none;
        }
        .attendance-ring-fill.attendance-ring-overtime {
            animation: none;
        }
    }
</style>
@endpush

@section('content')
    @php
        $tab = $tab ?? 'today';
        $counts = $counts ?? ['today' => 0, 'tomorrow' => 0, 'week' => 0, 'all' => 0];
        $lastType = $lastEvent?->event_type;
        $isPunchedIn = in_array($lastType, ['clock_in', 'break_end'], true);
        $isOnBreak = $lastType === 'break_start';
        $stateClass = $isPunchedIn ? 'state-in' : ($isOnBreak ? 'state-break' : 'state-out');
    @endphp

    <h1 class="visually-hidden">Dashboard</h1>

    <!-- Attendance Bar — Redesigned Component (Normal, Non-Sticky) -->
    <div class="attendance-bar-card card border-0 shadow-sm mb-3">
        <div class="attendance-bar-inner d-flex align-items-center justify-content-between flex-wrap py-2 px-3">
            
            <!-- Left cluster: Office Name & Status line with circular progress ring -->
            <div class="attendance-left-cluster d-flex align-items-center gap-2 flex-wrap">
                <div class="attendance-office-group d-flex align-items-center gap-1">
                    <i class="bi bi-building attendance-office-icon" aria-hidden="true"></i>
                    <span class="attendance-office-name">{{ $branch?->name ?? 'Head Office' }}</span>
                </div>

                <div class="attendance-status-group d-flex align-items-center gap-1 ms-1">
                    <!-- Progress Ring & Center Dot -->
                    <div class="attendance-ring-wrap d-inline-flex align-items-center justify-content-center" title="{{ $isPunchedIn ? 'Shift in progress' : ($isOnBreak ? 'On break' : 'Punched out') }}">
                        <svg class="attendance-ring-svg" width="16" height="16" viewBox="0 0 20 20">
                            <!-- Background track -->
                            <circle class="attendance-ring-track" cx="10" cy="10" r="7.5" fill="none" stroke-width="2"></circle>
                            <!-- Progress fill ring -->
                            <circle class="attendance-ring-fill {{ $stateClass }}" cx="10" cy="10" r="7.5" fill="none" stroke-width="2" stroke-linecap="round"
                                    stroke-dasharray="47.1238" stroke-dashoffset="47.1238" transform="rotate(-90 10 10)"></circle>
                        </svg>
                        <!-- Center dot -->
                        <span class="attendance-status-dot {{ $stateClass }}"></span>
                    </div>

                    <!-- Status Label -->
                    <span class="attendance-status-label {{ $stateClass }}" id="attendance-status-text">
                        @if($isPunchedIn)
                            IN
                        @elseif($isOnBreak)
                            BREAK
                        @else
                            OUT
                        @endif
                    </span>

                    <!-- Dot separator & Live Timer (mono digits) -->
                    <span class="attendance-timer-divider text-muted small">·</span>
                    <span class="attendance-live-timer mono" id="attendance-timer-digits">00:00:00</span>
                </div>
            </div>

            <!-- Desktop Divider -->
            <div class="attendance-divider d-none d-md-block"></div>

            <!-- Right cluster: Remarks + Actions form -->
            <form method="POST" action="{{ route('attendance.office-punch') }}" id="dash-punch-form" class="attendance-right-cluster d-flex align-items-center gap-2 ms-auto flex-wrap">
                @csrf
                <input type="hidden" name="event_type" id="dash-event-type" value="clock_in">
                <input type="hidden" name="latitude" id="dash-latitude">
                <input type="hidden" name="longitude" id="dash-longitude">
                <input type="hidden" name="gps_accuracy_meters" id="dash-accuracy">
                
                <!-- Ghost Remarks Input -->
                <div class="attendance-remarks-wrap">
                    <input type="text" name="remarks" id="dash-remarks-input" class="form-control attendance-remarks-input" placeholder="Remarks…">
                </div>

                <!-- Segmented Action Buttons -->
                <div class="attendance-actions-group d-flex align-items-center">
                    @if(! $isPunchedIn && ! $isOnBreak)
                        <button type="button" onclick="submitDashPunch('clock_in')" class="btn attendance-btn attendance-btn-primary" id="btn-punch-in">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Punch In
                        </button>
                    @else
                        @if($isPunchedIn)
                            <button type="button" onclick="submitDashPunch('break_start')" class="btn attendance-btn attendance-btn-outline" id="btn-punch-break">
                                <i class="bi bi-cup-hot me-1"></i>Break
                            </button>
                        @elseif($isOnBreak)
                            <button type="button" onclick="submitDashPunch('break_end')" class="btn attendance-btn attendance-btn-resume" id="btn-punch-resume">
                                <i class="bi bi-play-fill me-1"></i>Resume
                            </button>
                        @endif
                        <button type="button" onclick="submitDashPunch('clock_out')" class="btn attendance-btn attendance-btn-primary" id="btn-punch-out">
                            <i class="bi bi-box-arrow-right me-1"></i>Punch Out
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>

    <!-- Sticky: ONLY Tab Headers -->
    <div class="dash-sticky-tabs mb-3">
        <div class="my-tasks-tab-nav">
            <a href="#" class="my-tasks-tab-item dash-tab {{ $tab === 'today' ? 'active' : '' }}" data-tab="today">
                TODAY @if($counts['today'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['today'] }}</span>@endif
            </a>
            <a href="#" class="my-tasks-tab-item dash-tab {{ $tab === 'tomorrow' ? 'active' : '' }}" data-tab="tomorrow">
                TOMORROW @if($counts['tomorrow'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['tomorrow'] }}</span>@endif
            </a>
            <a href="#" class="my-tasks-tab-item dash-tab {{ $tab === 'week' ? 'active' : '' }}" data-tab="week">
                WEEK @if($counts['week'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['week'] }}</span>@endif
            </a>
            <a href="#" class="my-tasks-tab-item dash-tab {{ $tab === 'all' ? 'active' : '' }}" data-tab="all">
                ALL @if($counts['all'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['all'] }}</span>@endif
            </a>
            <span class="ms-auto d-none d-md-inline-flex align-items-center gap-3">
                <span class="mono extra-small text-muted d-inline-flex align-items-center gap-1">
                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                    <span data-clock></span> UTC
                </span>
                <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm">Full register</a>
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Task pane — same as task register -->
    <div class="card shadow-sm reveal" style="--d: 60ms">
        <div id="dash-task-list">
            @include('partials.task-list', ['tasks' => $tasks])
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var tick = function () {
            $('[data-clock]').each(function () {
                var el = $(this), h = new Date();
                el.text(String(h.getUTCHours()).padStart(2, '0') + ':' + String(h.getUTCMinutes()).padStart(2, '0') + ':' + String(h.getUTCSeconds()).padStart(2, '0'));
            });
        };
        tick();
        setInterval(tick, 1000);

        // Attendance Bar Progress Ring & Live Monospace Timer
        const isPunchedIn = {{ $isPunchedIn ? 'true' : 'false' }};
        const isOnBreak = {{ $isOnBreak ? 'true' : 'false' }};
        const serverWorkedSeconds = {{ (int) ($workedSecondsToday ?? 0) }};
        const activeAnchorMs = {{ !empty($activeAnchorMs) ? (int) $activeAnchorMs : 'null' }};
        const scheduledSeconds = {{ (int) ($scheduledShiftSeconds ?? 28800) }};
        const circumference = 47.1238; // 2 * PI * 7.5

        function formatHms(totalSec) {
            const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');
            return h + ':' + m + ':' + s;
        }

        function updateAttendanceBar() {
            let currentElapsedSec = serverWorkedSeconds;

            if (isPunchedIn && activeAnchorMs) {
                const deltaSec = Math.max(0, Math.floor((Date.now() - activeAnchorMs) / 1000));
                currentElapsedSec = serverWorkedSeconds + deltaSec;
                $('#attendance-timer-digits').text(formatHms(currentElapsedSec));
            } else if (isOnBreak) {
                $('#attendance-timer-digits').html(formatHms(serverWorkedSeconds) + ' <span class="extra-small text-warning mono" title="Clock paused" style="font-size: 10px;">❚❚</span>');
            } else {
                if (serverWorkedSeconds > 0) {
                    $('#attendance-timer-digits').text(formatHms(serverWorkedSeconds));
                } else {
                    $('#attendance-timer-digits').text('00:00:00');
                }
            }

            // Progress Ratio & Ring Offset
            const ratio = isPunchedIn || isOnBreak ? Math.min(1.0, currentElapsedSec / scheduledSeconds) : 0;
            const offset = circumference - (ratio * circumference);
            const $fill = $('.attendance-ring-fill');
            $fill.css('stroke-dashoffset', offset);

            // Signal Coral Overtime Cue past scheduled shift length
            if (isPunchedIn && currentElapsedSec > scheduledSeconds) {
                $fill.addClass('attendance-ring-overtime');
            } else {
                $fill.removeClass('attendance-ring-overtime');
            }
        }

        updateAttendanceBar();
        if (isPunchedIn) {
            setInterval(updateAttendanceBar, 1000);
        }

        // Geolocation prefetch for punch
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (pos) {
                $('#dash-latitude').val(pos.coords.latitude);
                $('#dash-longitude').val(pos.coords.longitude);
                $('#dash-accuracy').val(Math.round(pos.coords.accuracy));
            }, function () {}, { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 });
        }

        // AJAX tab switching — no page reload.
        $('.dash-tab').on('click', function (e) {
            e.preventDefault();
            var $tab = $(this);
            if ($tab.hasClass('active')) return;
            $('.dash-tab').removeClass('active');
            $tab.addClass('active');
            $('#dash-task-list').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Loading…</div>');
            axios.get('{{ route('dashboard') }}', { params: { tab: $tab.data('tab') } })
                .then(function (res) {
                    $('#dash-task-list').html(res.data.html);
                })
                .catch(function () {
                    $('#dash-task-list').html('<div class="text-center py-5 text-danger">Failed to load.</div>');
                });
        });

        function submitDashPunch(type) {
            $('#dash-event-type').val(type);
            $('#dash-punch-form').submit();
        }
        // Inline onclick handlers in the punch console call this globally.
        window.submitDashPunch = submitDashPunch;
    })(jQuery);
</script>
@endpush
