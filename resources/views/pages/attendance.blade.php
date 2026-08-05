@extends('layouts.app')

@section('title', 'Attendance Events')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Attendance · Events</span>
            <h2 class="h3 mt-1 mb-0">Attendance event log</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('attendance.corrections') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Corrections
            </a>
            <a href="{{ route('shifts') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar-range me-1" aria-hidden="true"></i>Shifts
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3 reveal" style="--d: 80ms" role="search">
        <div class="col-md-3">
            <label for="user_id" class="visually-hidden">Worker</label>
            <select name="user_id" id="user_id" class="form-select form-select-sm">
                <option value="">All workers</option>
                @foreach ($workers as $worker)
                    <option value="{{ $worker->id }}" @selected(request('user_id') == $worker->id)>{{ $worker->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="event_type" class="visually-hidden">Event type</label>
            <select name="event_type" id="event_type" class="form-select form-select-sm">
                <option value="">All event types</option>
                @foreach (\App\Models\AttendanceEvent::TYPES as $type)
                    <option value="{{ $type }}" @selected(request('event_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="from" class="visually-hidden">From</label>
            <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label for="to" class="visually-hidden">To</label>
            <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-sm btn-primary" type="submit">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
            @if(request()->anyFilled(['user_id', 'event_type', 'from', 'to']))
                <a href="{{ route('attendance') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Clear
                </a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 100ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Server time</th>
                        <th>Geo</th>
                        <th>Geofence</th>
                        <th>Flags</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td class="fw-semibold">{{ $event->user?->name }}</td>
                            <td><span class="status-badge status-muted">{{ str_replace('_', ' ', $event->event_type) }}</span></td>
                            <td class="small">{{ $event->server_timestamp?->format('j M Y H:i:s') }}</td>
                            <td class="small text-muted">
                                @if($event->latitude)
                                    {{ round($event->latitude, 5) }}, {{ round($event->longitude, 5) }}<br>
                                    <span class="text-muted">acc {{ $event->gps_accuracy_meters ?? '—' }}m</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($event->inside_geofence !== null)
                                    <span class="status-badge status-{{ $event->inside_geofence ? 'active' : 'danger' }}">
                                        {{ $event->inside_geofence ? 'inside' : 'outside' }}
                                    </span>
                                    @if($event->distance_from_property_meters !== null)
                                        <br><small class="text-muted">{{ round($event->distance_from_property_meters) }}m / {{ $event->effective_radius_meters }}m</small>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!empty($event->integrity_flags))
                                    @foreach (array_keys($event->integrity_flags) as $flag)
                                        <span class="status-badge status-warning">{{ str_replace('_', ' ', $flag) }}</span>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $event->source }}
                                @if($event->offline) · offline @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
                                    No attendance events yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $events->links() }}</div>
@endsection
