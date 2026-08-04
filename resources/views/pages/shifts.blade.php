@extends('layouts.app')

@section('title', 'Shifts')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Attendance · Shifts</span>
            <h2 class="h3 mt-1 mb-0">Shift board</h2>
        </div>
        <a href="{{ route('attendance') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1" aria-hidden="true"></i>Attendance events
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3 reveal" style="--d: 80ms">
        <div class="col-md-3">
            <label for="date" class="visually-hidden">Date</label>
            <input type="date" id="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label for="user_id" class="visually-hidden">Worker</label>
            <select name="user_id" id="user_id" class="form-select form-select-sm">
                <option value="">All workers</option>
                @foreach ($workers as $worker)
                    <option value="{{ $worker->id }}" @selected(request('user_id') == $worker->id)>{{ $worker->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="visually-hidden">Status</label>
            <select name="status" id="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                @foreach (['scheduled', 'confirmed', 'in_progress', 'completed', 'missed', 'cancelled', 'absent'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
        </div>
    </form>

    @if(auth()->user()->hasPermission('5.2'))
        <div class="card shadow-sm mb-4 reveal" style="--d: 120ms">
            <div class="card-header mono">New shift</div>
            <div class="card-body">
                <form method="POST" action="{{ route('shifts.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <label for="user_id" class="form-label visually-hidden">Worker</label>
                        <select name="user_id" id="new-user_id" class="form-select form-select-sm" required>
                            <option value="">Worker</option>
                            @foreach ($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date" class="form-label visually-hidden">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label for="scheduled_start_at" class="form-label visually-hidden">Start</label>
                        <input type="datetime-local" name="scheduled_start_at" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label for="scheduled_end_at" class="form-label visually-hidden">End</label>
                        <input type="datetime-local" name="scheduled_end_at" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label for="property_id" class="form-label visually-hidden">Property</label>
                        <select name="property_id" class="form-select form-select-sm">
                            <option value="">Property</option>
                            @foreach (\App\Models\Property::orderBy('name')->get(['id', 'name']) as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-plus me-1" aria-hidden="true"></i>Add shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card shadow-sm reveal" style="--d: 160ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>When</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Summary</th>
                        @if(auth()->user()->hasPermission('5.2'))<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        @php $summary = $rules->summarize($shift); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $shift->user?->name }}</td>
                            <td class="small">
                                {{ $shift->scheduled_start_at->format('D j M H:i') }} → {{ $shift->scheduled_end_at->format('H:i') }}
                                @if($summary['late'])<br><span class="status-badge status-warning">late</span>@endif
                                @if($summary['early_departure'])<span class="status-badge status-warning">early</span>@endif
                                @if($summary['missed'])<span class="status-badge status-danger">missed</span>@endif
                            </td>
                            <td>{{ $shift->property?->name ?? '—' }}</td>
                            <td>
                                <span class="status-badge status-{{ in_array($shift->status, ['completed', 'confirmed']) ? 'active' : ($shift->status === 'in_progress' ? 'warning' : (in_array($shift->status, ['missed', 'absent', 'cancelled']) ? 'danger' : 'muted')) }}">
                                    {{ str_replace('_', ' ', $shift->status) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                worked {{ floor($summary['worked_minutes'] / 60) }}h{{ $summary['worked_minutes'] % 60 }}m ·
                                break {{ $summary['break_minutes'] }}m ·
                                overtime {{ $summary['overtime_minutes'] }}m
                            </td>
                            @if(auth()->user()->hasPermission('5.2'))
                                <td class="text-end">
                                    <form method="POST" action="{{ route('shifts.update', $shift) }}" class="d-flex gap-1 justify-content-end">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm" style="max-width: 140px">
                                            @foreach (['scheduled', 'confirmed', 'in_progress', 'completed', 'missed', 'cancelled', 'absent'] as $status)
                                                <option value="{{ $status }}" @selected($shift->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary">Set</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-calendar-range"></i></span>
                                    No shifts match the filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $shifts->links() }}</div>
@endsection
