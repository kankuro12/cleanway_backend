@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Data · Reports</span>
            <h2 class="h3 mt-1 mb-0">Report centre</h2>
        </div>
        @if(auth()->user()->hasPermission('7.2'))
            <form method="POST" action="{{ route('reports.export') }}">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                @foreach (request()->query() as $key => $value)
                    @if(is_string($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                @endforeach
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-download me-1" aria-hidden="true"></i>Queue CSV export
                </button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    @include('partials.compact-filter-bar', ['searchNames' => []])

    <form method="GET" id="filter-form" class="filter-form mb-3 reveal" style="--d: 80ms">
        <div class="filter-sheet-head">
            <span class="mono text-muted">Filter options</span>
            <button type="button" class="btn btn-icon-touch" data-filter-close aria-label="Close filters">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="row g-2 filter-sheet-body">
        <div class="col-md-2">
            <label for="type" class="visually-hidden">Report</label>
            <select name="type" id="type" class="form-select form-select-sm">
                @foreach (['tasks' => 'Tasks', 'attendance' => 'Attendance', 'approvals' => 'Approvals', 'properties' => 'Properties', 'incidents' => 'Incidents'] as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
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
        @if($type === 'tasks' || $type === 'attendance')
            <div class="col-md-2">
                <label for="status" class="visually-hidden">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (['draft', 'scheduled', 'unassigned', 'assigned', 'accepted', 'in_progress', 'paused', 'completed', 'submitted_for_approval', 'approved', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($type === 'tasks')
            <div class="col-md-2">
                <label for="task_type_id" class="visually-hidden">Type</label>
                <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach ($taskTypes as $taskType)
                        <option value="{{ $taskType->id }}" @selected(request('task_type_id') == $taskType->id)>{{ $taskType->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if(in_array($type, ['tasks', 'attendance']))
            <div class="col-md-2">
                <label for="user_id" class="visually-hidden">User</label>
                <select name="user_id" id="user_id" class="form-select form-select-sm">
                    <option value="">All users</option>
                    @foreach ($workers as $worker)
                        <option value="{{ $worker->id }}" @selected(request('user_id') == $worker->id)>{{ $worker->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($type === 'incidents')
            <div class="col-md-2">
                <label for="category" class="visually-hidden">Category</label>
                <select name="category" id="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach (['property_access_problem', 'missing_key', 'incorrect_access_code', 'damaged_equipment', 'property_damage', 'safety_hazard', 'missing_supplies', 'unsafe_situation', 'task_cannot_be_completed', 'other'] as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-2 d-none d-md-block">
            <button class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Run
            </button>
        </div>
        </div>
        <div class="filter-sheet-foot">
            <button type="submit" class="btn btn-touch w-100">Apply filters</button>
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="d-lg-none p-3 d-flex flex-column gap-2">
            @forelse ($report['rows'] as $row)
                @php $meta = collect($row)->slice(1, 3)->map(fn ($c) => $c ?? '—')->implode(' · '); @endphp
                <div class="mobile-task-card compact">
                    <div class="mtc-title">{{ $row[0] ?? '—' }}</div>
                    <div class="mtc-meta mt-1">{{ $meta }}</div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-bar-chart"></i></span>
                    No rows for this report + filters.
                </div>
            @endforelse
        </div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        @foreach ($report['headers'] as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="small">{{ $cell ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(1, count($report['headers'])) }}">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-bar-chart"></i></span>
                                    No rows for this report + filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mt-4 reveal" style="--d: 200ms">
        <div class="card-header mono">My exports</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Type</th><th>Status</th><th>Requested</th><th class="text-end">File</th></tr>
                </thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr>
                            <td class="small">{{ $export->type }}</td>
                            <td><span class="status-badge status-{{ $export->status === 'done' ? 'active' : ($export->status === 'failed' ? 'danger' : 'warning') }}">{{ $export->status }}</span></td>
                            <td class="small text-muted">{{ $export->requested_at?->format('j M H:i') }}</td>
                            <td class="text-end">
                                @if($export->status === 'done')
                                    <a href="{{ route('reports.download', $export) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-arrow-down me-1" aria-hidden="true"></i>CSV
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted small">No exports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
