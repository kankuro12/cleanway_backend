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

    .attendance-bar-card {
        background: var(--att-paper, #F7F8FA) !important;
        border: 1px solid var(--att-line, #E4E7EC) !important;
        border-radius: 12px;
        transition: border-color 0.2s ease;
    }

    .attendance-bar-inner {
        min-height: 48px;
    }

    .attendance-office-icon {
        font-size: 15px;
        color: var(--att-muted, #8A8F98);
        line-height: 1;
    }

    .attendance-office-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--att-ink, #14181F);
        line-height: 1.2;
        white-space: nowrap;
    }

    .attendance-status-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .attendance-status-label.state-in { color: var(--att-live-green, #1F9D63); }
    .attendance-status-label.state-break { color: var(--att-amber, #E0A100); }
    .attendance-status-label.state-out { color: var(--att-muted, #8A8F98); }

    .attendance-live-timer {
        font-family: var(--cw-font-mono, 'IBM Plex Mono', monospace);
        font-size: 12px;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
        color: var(--att-muted, #8A8F98);
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

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

    .attendance-ring-track { stroke: var(--att-line, #E4E7EC); }
    .attendance-ring-fill { transition: stroke-dashoffset 0.4s ease, stroke 0.25s ease; }
    .attendance-ring-fill.state-in { stroke: var(--att-live-green, #1F9D63); }
    .attendance-ring-fill.state-break { stroke: var(--att-amber, #E0A100); }
    .attendance-ring-fill.state-out { stroke: var(--att-line, #E4E7EC); }
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
    .attendance-status-dot.state-break { background-color: var(--att-amber, #E0A100); }
    .attendance-status-dot.state-out { background-color: var(--att-muted, #8A8F98); }

    @keyframes att-breathing {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1.0; }
    }

    .attendance-divider {
        width: 1px;
        height: 20px;
        background: var(--att-line, #E4E7EC);
        margin: 0 10px 0 6px;
    }

    .attendance-remarks-input {
        background: transparent !important;
        border: 1px solid transparent !important;
        height: 32px;
        min-height: 32px;
        border-radius: 6px;
        padding: 0 10px;
        font-size: 12px;
        color: var(--att-ink, #14181F);
        width: 140px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .attendance-remarks-input:hover,
    .attendance-remarks-input:focus {
        background: #ffffff !important;
        border-color: var(--att-line, #E4E7EC) !important;
        outline: none;
    }

    .attendance-btn {
        height: 32px;
        min-height: 32px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        cursor: pointer;
        transition: transform 120ms ease;
    }

    .attendance-btn:active { transform: scale(0.97); }
    .attendance-btn-primary {
        background: var(--att-ink, #14181F) !important;
        color: #ffffff !important;
        border: 1px solid var(--att-ink, #14181F) !important;
    }
    .attendance-btn-primary:hover {
        background: #232a35 !important;
        border-color: #232a35 !important;
    }
    .attendance-btn-outline {
        background: transparent !important;
        border: 1px solid var(--att-line, #E4E7EC) !important;
        color: var(--att-ink, #14181F) !important;
    }
    .attendance-btn-resume {
        background: transparent !important;
        border: 1px solid var(--att-amber, #E0A100) !important;
        color: var(--att-amber, #E0A100) !important;
    }

    /* Modal Due Time Preset Buttons */
    .time-preset-btn {
        font-family: var(--cw-font-mono, monospace);
        font-size: 13px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid var(--prop-divider, #EAEAEC);
        background: #F8FAFC;
        color: var(--prop-text-primary, #1C1C1E);
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .time-preset-btn:hover,
    .time-preset-btn.active {
        background: var(--prop-accent-blue, #3AA9E0);
        border-color: var(--prop-accent-blue, #3AA9E0);
        color: #FFFFFF;
    }
</style>
@endpush

@section('content')
    @php
        $propertyGroups = $propertyGroups ?? collect();
        $tab = $tab ?? 'today';
        $statusFilter = $statusFilter ?? 'not_started';
        $statCounts = $statCounts ?? ['not_started' => 0, 'in_progress' => 0, 'completed' => 0, 'issues' => 0, 'total' => 0];
        $targetDate = $targetDate ?? '';
        $sort = 'suggested';

        $lastType = $lastEvent?->event_type ?? null;
        $isPunchedIn = in_array($lastType, ['clock_in', 'break_end'], true);
        $isOnBreak = $lastType === 'break_start';
        $stateClass = $isPunchedIn ? 'state-in' : ($isOnBreak ? 'state-break' : 'state-out');
    @endphp

    <h1 class="visually-hidden">Property Operations Dashboard</h1>

    <!-- 2. Attendance Bar (Docked cleanly above metric cards) -->
    <div class="attendance-bar-card card border-0 shadow-sm mb-3">
        <div class="attendance-bar-inner d-flex align-items-center justify-content-between py-2 px-2 px-md-3">
            <!-- Left cluster: Office Name & Status line with circular progress ring (Hidden on mobile) -->
            <div class="d-none d-md-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-1">
                    <i class="bi bi-building attendance-office-icon" aria-hidden="true"></i>
                    <span class="attendance-office-name">{{ $branch?->name ?? 'Head Office' }}</span>
                </div>

                <div class="d-flex align-items-center gap-1 ms-1">
                    <div class="attendance-ring-wrap d-inline-flex align-items-center justify-content-center" title="{{ $isPunchedIn ? 'Shift in progress' : ($isOnBreak ? 'On break' : 'Punched out') }}">
                        <svg class="attendance-ring-svg" width="16" height="16" viewBox="0 0 20 20">
                            <circle class="attendance-ring-track" cx="10" cy="10" r="7.5" fill="none" stroke-width="2"></circle>
                            <circle class="attendance-ring-fill {{ $stateClass }}" cx="10" cy="10" r="7.5" fill="none" stroke-width="2" stroke-linecap="round"
                                    stroke-dasharray="47.1238" stroke-dashoffset="47.1238" transform="rotate(-90 10 10)"></circle>
                        </svg>
                        <span class="attendance-status-dot {{ $stateClass }}"></span>
                    </div>

                    <span class="attendance-status-label {{ $stateClass }}" id="attendance-status-text">
                        @if($isPunchedIn) IN @elseif($isOnBreak) BREAK @else OUT @endif
                    </span>

                    <span class="text-muted small">·</span>
                    <span class="attendance-live-timer mono" id="attendance-timer-digits">00:00:00</span>
                </div>
            </div>

            <!-- Desktop Divider -->
            <div class="attendance-divider d-none d-md-block"></div>

            <!-- Right cluster: Remarks + Actions form (Single clean line on mobile) -->
            <form method="POST" action="{{ route('attendance.office-punch') }}" id="dash-punch-form" class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0 ms-md-auto">
                @csrf
                <input type="hidden" name="event_type" id="dash-event-type" value="clock_in">
                <input type="hidden" name="latitude" id="dash-latitude">
                <input type="hidden" name="longitude" id="dash-longitude">
                <input type="hidden" name="gps_accuracy_meters" id="dash-accuracy">
                
                <div class="d-none d-sm-block">
                    <input type="text" name="remarks" id="dash-remarks-input" class="form-control attendance-remarks-input" placeholder="Remarks…">
                </div>

                <div class="d-flex align-items-center gap-2 w-100 justify-content-center justify-content-md-end">
                    @if(! $isPunchedIn && ! $isOnBreak)
                        <button type="button" onclick="submitDashPunch('clock_in')" class="btn attendance-btn attendance-btn-primary flex-fill flex-md-grow-0" id="btn-punch-in">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Punch In
                        </button>
                    @else
                        @if($isPunchedIn)
                            <button type="button" onclick="submitDashPunch('break_start')" class="btn attendance-btn attendance-btn-outline flex-fill flex-md-grow-0" id="btn-punch-break">
                                <i class="bi bi-cup-hot me-1"></i>Break
                            </button>
                        @elseif($isOnBreak)
                            <button type="button" onclick="submitDashPunch('break_end')" class="btn attendance-btn attendance-btn-resume flex-fill flex-md-grow-0" id="btn-punch-resume">
                                <i class="bi bi-play-fill me-1"></i>Resume
                            </button>
                        @endif
                        <button type="button" onclick="submitDashPunch('clock_out')" class="btn attendance-btn attendance-btn-primary flex-fill flex-md-grow-0" id="btn-punch-out">
                            <i class="bi bi-box-arrow-right me-1"></i>Punch Out
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- 3. 4-Up Stat Cards Row (Tappable Quick Filters) -->
    <div class="prop-stat-row mb-3">
        <!-- Not started (Neutral Black) -->
        <div class="prop-stat-card {{ $statusFilter === 'not_started' ? 'active' : '' }}" data-status-filter="not_started" title="Filter by Not Started tasks">
            <span class="prop-stat-label">Not started</span>
            <span class="prop-stat-num neutral" id="stat-count-not-started">{{ $statCounts['not_started'] }}</span>
        </div>

        <!-- In Progress (Green) -->
        <div class="prop-stat-card {{ $statusFilter === 'in_progress' ? 'active' : '' }}" data-status-filter="in_progress" title="Filter by In Progress tasks">
            <span class="prop-stat-label">In Progress</span>
            <span class="prop-stat-num green" id="stat-count-in-progress">{{ $statCounts['in_progress'] }}</span>
        </div>

        <!-- Completed (Green) -->
        <div class="prop-stat-card {{ $statusFilter === 'completed' ? 'active' : '' }}" data-status-filter="completed" title="Filter by Completed tasks">
            <span class="prop-stat-label">Completed</span>
            <span class="prop-stat-num green" id="stat-count-completed">{{ $statCounts['completed'] }}</span>
        </div>

        <!-- Issues (Red) -->
        <div class="prop-stat-card {{ $statusFilter === 'issues' ? 'active' : '' }}" data-status-filter="issues" title="Filter by Issues">
            <span class="prop-stat-label">Issues</span>
            <span class="prop-stat-num red" id="stat-count-issues">{{ $statCounts['issues'] }}</span>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4. Property Ops Task Feed Container (Grouped by Property) -->
    <div id="prop-ops-feed-container">
        @include('partials.property-ops-feed', [
            'propertyGroups' => $propertyGroups,
            'statusFilter' => $statusFilter,
        ])
    </div>

    <!-- 5. Floating Action Button (FAB) (Blue Circle fixed bottom-right) -->
    @if(auth()->user()->hasPermission('4.2'))
        <a href="{{ route('tasks.create') }}" class="prop-fab" title="Create New Task" aria-label="Create Task">
            <i class="bi bi-plus-lg"></i>
        </a>
    @endif

    <!-- Modal: Inline "Edit Date & Time" Schedule Setter -->
    <div class="modal fade" id="modal-due-time" tabindex="-1" aria-labelledby="modalDueTimeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-2 px-3 border-bottom">
                    <div>
                        <h5 class="modal-title fs-6 fw-bold mb-0" id="modalDueTimeLabel"><i class="bi bi-calendar-event me-1 text-primary"></i>Set Date & Time</h5>
                        <small class="text-muted extra-small" id="modal-task-title-display">Task</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" id="due-time-task-id">

                    <!-- Schedule Date -->
                    <label for="input-custom-due-date" class="form-label extra-small text-muted mb-1">Scheduled Date <span class="text-danger">*</span></label>
                    <input type="date" id="input-custom-due-date" class="form-control form-control-sm mb-2" value="{{ today()->toDateString() }}">

                    <!-- Quick Preset Time Buttons -->
                    <label class="form-label extra-small text-muted mb-1">Quick Time Presets</label>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <button type="button" class="time-preset-btn" data-time="08:00">08:00 AM</button>
                        <button type="button" class="time-preset-btn" data-time="09:00">09:00 AM</button>
                        <button type="button" class="time-preset-btn" data-time="10:00">10:00 AM</button>
                        <button type="button" class="time-preset-btn" data-time="12:00">12:00 PM</button>
                        <button type="button" class="time-preset-btn" data-time="14:00">02:00 PM</button>
                        <button type="button" class="time-preset-btn" data-time="16:00">04:00 PM</button>
                    </div>

                    <!-- Custom Time Field -->
                    <label for="input-custom-due-time" class="form-label extra-small text-muted mb-1">Scheduled Time <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="time" id="input-custom-due-time" class="form-control font-monospace" value="09:00">
                    </div>

                    <div id="due-time-error" class="alert alert-danger py-1 px-2 extra-small d-none mb-2"></div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btn-save-due-time">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="save-time-spinner" role="status"></span>
                            Save Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function ($) {
        // Live UTC Clock
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

        function submitDashPunch(type) {
            $('#dash-event-type').val(type);
            $('#dash-punch-form').submit();
        }
        window.submitDashPunch = submitDashPunch;

        // Current Dashboard State
        let currentTab = '{{ $tab }}';
        let currentDate = '{{ $targetDate }}';
        let currentStatusFilter = '{{ $statusFilter }}';
        let currentSort = '{{ $sort }}';
        let currentSearch = '{{ $search }}';

        // AJAX Dashboard Refresh Function
        function fetchDashboardFeed() {
            $('#prop-ops-feed-container').css('opacity', '0.5');
            
            axios.get('{{ route('dashboard') }}', {
                params: {
                    tab: currentTab,
                    date: currentTab === 'all' ? '' : currentDate,
                    status: currentStatusFilter,
                    sort: currentSort,
                    search: currentSearch
                }
            })
            .then(function (res) {
                $('#prop-ops-feed-container').html(res.data.html).css('opacity', '1');
                
                // Update stats
                if (res.data.stat_counts) {
                    $('#stat-count-not-started').text(res.data.stat_counts.not_started);
                    $('#stat-count-in-progress').text(res.data.stat_counts.in_progress);
                    $('#stat-count-completed').text(res.data.stat_counts.completed);
                    $('#stat-count-issues').text(res.data.stat_counts.issues);
                }

                if (res.data.active_date_label) {
                    $('#label-active-date').text(res.data.active_date_label);
                }
            })
            .catch(function () {
                $('#prop-ops-feed-container').html('<div class="alert alert-danger my-3">Failed to load property dashboard. Please try again.</div>').css('opacity', '1');
            });
        }

        // Stat Card Quick Filter Click
        $(document).on('click', '.prop-stat-card', function () {
            const filter = $(this).data('status-filter');
            if (currentStatusFilter === filter) {
                // Toggle off to all
                currentStatusFilter = 'all';
                $('.prop-stat-card').removeClass('active');
            } else {
                currentStatusFilter = filter;
                $('.prop-stat-card').removeClass('active');
                $(this).addClass('active');
            }
            fetchDashboardFeed();
        });

        // Property Group Accordion Collapse Toggle
        $(document).on('click', '[data-toggle="prop-collapse"]', function () {
            const target = $(this).data('target');
            $(target).toggleClass('collapsed');
        });

        // Inline "Set Date & Time" Trigger Click
        $(document).on('click', '.btn-due-time-trigger', function (e) {
            e.stopPropagation();
            const taskId = $(this).data('task-id');
            const taskTitle = $(this).data('task-title');
            const currentTime = $(this).data('current-time') || '09:00';
            const currentDateVal = $(this).data('current-date') || '{{ today()->toDateString() }}';

            $('#due-time-task-id').val(taskId);
            $('#input-custom-due-date').val(currentDateVal);
            $('#modal-task-title-display').text(taskTitle);
            $('#input-custom-due-time').val(currentTime.substring(0, 5) || '09:00');
            $('#due-time-error').addClass('d-none').text('');

            $('.time-preset-btn').removeClass('active');
            $(`.time-preset-btn[data-time="${currentTime.substring(0, 5)}"]`).addClass('active');

            $('#modal-due-time').data('custom-hash', '#schedule-' + taskId);
            const modal = new bootstrap.Modal(document.getElementById('modal-due-time'));
            modal.show();
        });

        // Time Preset Click in Due Time Modal
        $('.time-preset-btn').on('click', function () {
            $('.time-preset-btn').removeClass('active');
            $(this).addClass('active');
            $('#input-custom-due-time').val($(this).data('time'));
        });

        // Save Due Time & Date via AJAX
        $('#btn-save-due-time').on('click', function () {
            const taskId = $('#due-time-task-id').val();
            const dueTime = $('#input-custom-due-time').val();
            const dueDate = $('#input-custom-due-date').val();

            if (!dueTime) {
                $('#due-time-error').removeClass('d-none').text('Please select or enter a valid time.');
                return;
            }

            if (!dueDate) {
                $('#due-time-error').removeClass('d-none').text('Please select a valid date.');
                return;
            }

            $('#save-time-spinner').removeClass('d-none');
            $('#btn-save-due-time').prop('disabled', true);
            $('#due-time-error').addClass('d-none');

            axios.post('/admin/tasks/' + taskId + '/due-time', {
                due_time: dueTime,
                due_date: dueDate
            })
            .then(function (res) {
                $('#save-time-spinner').addClass('d-none');
                $('#btn-save-due-time').prop('disabled', false);

                if (res.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal-due-time'))?.hide();

                    if (currentTab !== 'all' && res.data.date_val !== currentDate) {
                        // Rescheduled to a different date than currently viewed: refresh feed & stat counts
                        fetchDashboardFeed();
                    } else {
                        // Update the stub in the task row directly with both Date and Time
                        const timeCol = $(`#task-row-${taskId} .prop-time-col`);
                        timeCol.html(`
                            <div class="prop-date-val" id="date-val-${taskId}">${res.data.formatted_date}</div>
                            <div class="prop-time-val" id="time-val-${taskId}">${res.data.time_val}</div>
                            <div class="prop-time-ampm" id="time-ampm-${taskId}">${res.data.ampm}</div>
                        `);
                        timeCol.data('current-time', res.data.raw_time);
                        timeCol.data('current-date', res.data.date_val);

                        // Flash feedback
                        timeCol.css('background', '#DCFCE7');
                        setTimeout(() => timeCol.css('background', ''), 1200);
                    }
                }
            })
            .catch(function (err) {
                $('#save-time-spinner').addClass('d-none');
                $('#btn-save-due-time').prop('disabled', false);
                const msg = err.response?.data?.message || 'Failed to update schedule.';
                $('#due-time-error').removeClass('d-none').text(msg);
            });
        });

    })(jQuery);
</script>
@endpush
