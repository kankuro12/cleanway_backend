@extends('layouts.app')

@section('title', 'Shift Report')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-4">
        <span class="eyebrow">Reports · Attendance & Operations</span>
        <h1 class="h2 text-strong mb-1">Shift & Office Attendance Report</h1>
        <p class="text-muted small mb-0">Summary of personnel shift hours, office geofence compliance, and punch logs.</p>
    </div>

    <!-- Filter Console -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
            <span class="mono text-xs text-uppercase letter-spacing-1"><i class="bi bi-funnel me-1"></i>Report Filters</span>
            @if(request()->anyFilled(['from', 'to', 'branch_id', 'user_id', 'status']))
                <a href="{{ route('reports.shifts') }}" class="btn btn-sm btn-outline-light py-0 px-2 text-xs">Clear Filters</a>
            @endif
        </div>
        <div class="card-body bg-surface-2 p-3">
            <form method="GET" action="{{ route('reports.shifts') }}" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="filter-from" class="form-label mono text-xs text-uppercase fw-bold">From Date</label>
                    <input type="date" id="filter-from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="filter-to" class="form-label mono text-xs text-uppercase fw-bold">To Date</label>
                    <input type="date" id="filter-to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-branch" class="form-label mono text-xs text-uppercase fw-bold">Branch / Office</label>
                    <select id="filter-branch" name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-user" class="form-label mono text-xs text-uppercase fw-bold">Personnel</label>
                    <select id="filter-user" name="user_id" class="form-select form-select-sm">
                        <option value="">All Personnel</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}" @selected(request('user_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="filter-status" class="form-label mono text-xs text-uppercase fw-bold">Shift Status</label>
                    <select id="filter-status" name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="missed" @selected(request('status') === 'missed')>Missed</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-search me-1"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-primary shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">Total Shifts</span>
                <span class="fs-3 fw-bold text-strong d-block">{{ number_format($metrics['total_shifts']) }}</span>
                <span class="text-xs text-muted">{{ $metrics['completed_shifts'] }} completed</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-info shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">Worked Hours</span>
                <span class="fs-3 fw-bold text-info d-block">{{ number_format($metrics['total_worked_hours'], 1) }} <small class="fs-6">hrs</small></span>
                <span class="text-xs text-muted">Cumulative work</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-success shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">On-Time Punch Rate</span>
                <span class="fs-3 fw-bold text-success d-block">{{ $metrics['on_time_rate'] }}%</span>
                <span class="text-xs text-muted">Punctual check-ins</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-warning shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">Geofence Compliance</span>
                <span class="fs-3 fw-bold text-warning d-block">{{ $metrics['geofence_compliance_rate'] }}%</span>
                <span class="text-xs text-muted">Inside office radius</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-danger shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">Late Punch-Ins</span>
                <span class="fs-3 fw-bold text-danger d-block">{{ number_format($metrics['late_count']) }}</span>
                <span class="text-xs text-muted">> 5 mins past schedule</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="card p-3 border-start border-4 border-secondary shadow-sm h-100">
                <span class="mono text-xs text-muted text-uppercase d-block mb-1">Early Departures</span>
                <span class="fs-3 fw-bold text-secondary d-block">{{ number_format($metrics['early_departure_count']) }}</span>
                <span class="text-xs text-muted">Punched out early</span>
            </div>
        </div>
    </div>

    <!-- Shifts Data Register -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 text-strong mb-0 mono text-uppercase"><i class="bi bi-table me-2 text-primary"></i>Shift Punch Logs</h2>
            <span class="mono text-xs text-muted">Showing {{ $shifts->firstItem() ?? 0 }}-{{ $shifts->lastItem() ?? 0 }} of {{ $shifts->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light mono text-xs text-uppercase">
                    <tr>
                        <th>Personnel</th>
                        <th>Shift Date & Schedule</th>
                        <th>Punch In (GPS / Geofence)</th>
                        <th>Punch Out (GPS / Geofence)</th>
                        <th>Worked / Break</th>
                        <th>Shift Status</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($shifts as $shift)
                        @php
                            $summary = $rules->summarize($shift);
                            $cIn = $summary['clock_in_event'];
                            $cOut = $summary['clock_out_event'];
                            $isOfficePunchIn = $cIn && $cIn->task_id === null && $cIn->property_id === null;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-strong">{{ $shift->user?->name ?? 'Unassigned' }}</div>
                                <div class="mono text-xs text-muted">{{ $shift->user?->branch?->name ?? 'General Staff' }}</div>
                            </td>
                            <td>
                                <div class="mono text-dark fw-medium">{{ $shift->date->format('Y-m-d (D)') }}</div>
                                <div class="mono text-xs text-muted">
                                    {{ $shift->scheduled_start_at?->format('H:i') }} – {{ $shift->scheduled_end_at?->format('H:i') }}
                                </div>
                            </td>
                            <td>
                                @if($cIn)
                                    <div class="mono text-dark fw-bold">{{ $cIn->server_timestamp->format('H:i:s') }}</div>
                                    @if($cIn->inside_geofence)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mono text-xs">
                                            <i class="bi bi-geo-alt-fill me-1"></i>Inside Office ({{ (int)$cIn->distance_from_property_meters }}m)
                                        </span>
                                    @elseif($cIn->inside_geofence === false)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mono text-xs">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Outside Office ({{ (int)$cIn->distance_from_property_meters }}m)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary mono text-xs">No GPS</span>
                                    @endif
                                @else
                                    <span class="text-muted italic">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cOut)
                                    <div class="mono text-dark fw-bold">{{ $cOut->server_timestamp->format('H:i:s') }}</div>
                                    @if($cOut->inside_geofence)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mono text-xs">
                                            <i class="bi bi-geo-alt-fill me-1"></i>Inside Office ({{ (int)$cOut->distance_from_property_meters }}m)
                                        </span>
                                    @elseif($cOut->inside_geofence === false)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mono text-xs">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Outside Office ({{ (int)$cOut->distance_from_property_meters }}m)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary mono text-xs">No GPS</span>
                                    @endif
                                @else
                                    <span class="text-muted italic">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="mono text-dark fw-bold">
                                    {{ floor($summary['worked_minutes'] / 60) }}h {{ $summary['worked_minutes'] % 60 }}m
                                </div>
                                <div class="mono text-xs text-muted">Break: {{ $summary['break_minutes'] }}m</div>
                            </td>
                            <td>
                                <span class="status-badge @switch($shift->status)
                                    @case('completed') bg-success-subtle text-success @break
                                    @case('in_progress') bg-info-subtle text-info @break
                                    @case('scheduled') bg-primary-subtle text-primary @break
                                    @case('missed') bg-danger-subtle text-danger @break
                                    @default bg-secondary-subtle text-secondary
                                @endswitch px-2 py-1 rounded">
                                    <span class="dot"></span>
                                    <span class="mono text-xs text-uppercase">{{ str_replace('_', ' ', $shift->status) }}</span>
                                </span>
                                @if($summary['late'])
                                    <span class="badge bg-danger text-white mono text-xs ms-1">LATE</span>
                                @endif
                                @if($summary['early_departure'])
                                    <span class="badge bg-warning text-dark mono text-xs ms-1">EARLY</span>
                                @endif
                            </td>
                            <td>
                                @if($isOfficePunchIn || ($cIn === null && $shift->property_id === null))
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mono text-xs">
                                        <i class="bi bi-building me-1"></i>Office Punch
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle mono text-xs">
                                        <i class="bi bi-list-task me-1"></i>Task Punch
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                                No shift punch logs found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shifts->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
