@extends('layouts.app')

@section('title', 'Incidents')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Safety · Incidents</span>
            <h2 class="h3 mt-1 mb-0">Incident register</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('incidents') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>All incidents
            </a>
            @if(auth()->user()->hasPermission('8.2') || auth()->user()->hasPermission('4.4'))
                <a href="{{ route('incidents.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus me-1" aria-hidden="true"></i>Raise incident
                </a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
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
        <div class="col-md-3">
            <label for="status" class="visually-hidden">Status</label>
            <select name="status" id="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                @foreach (['open', 'acknowledged', 'investigating', 'resolved', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="category" class="visually-hidden">Category</label>
            <select name="category" id="category" class="form-select form-select-sm">
                <option value="">All categories</option>
                @foreach (['property_access_problem', 'missing_key', 'incorrect_access_code', 'damaged_equipment', 'property_damage', 'safety_hazard', 'missing_supplies', 'unsafe_situation', 'task_cannot_be_completed', 'other'] as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-none d-md-block">
            <button class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
        </div>
        </div>
        <div class="filter-sheet-foot">
            <button type="submit" class="btn btn-touch w-100">Apply filters</button>
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 120ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Incident</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Reporter</th>
                        @if(auth()->user()->hasPermission('8.2'))<th class="text-end">Act</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td data-label="Incident">
                                <span class="fw-semibold text-dark">#{{ $incident->id }}</span>
                                <br><small class="text-muted">{{ Str::limit($incident->description, 60) }}</small>
                            </td>
                            <td data-label="Category" class="small">{{ ucfirst(str_replace('_', ' ', $incident->category)) }}</td>
                            <td data-label="Severity"><span class="status-badge status-{{ $incident->severity === 'critical' ? 'danger' : ($incident->severity === 'high' ? 'warning' : 'muted') }}">{{ $incident->severity }}</span></td>
                            <td data-label="Status"><span class="status-badge status-{{ $incident->status === 'closed' || $incident->status === 'resolved' ? 'active' : ($incident->status === 'open' ? 'danger' : 'warning') }}">{{ $incident->status }}</span></td>
                            <td data-label="Reporter" class="small">{{ $incident->reporter?->name }}</td>
                            @if(auth()->user()->hasPermission('8.2'))
                                <td data-label="Act" class="text-end">
                                    <form method="POST" action="{{ route('incidents.transition', $incident) }}" class="d-flex gap-1 justify-content-end">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="max-width: 150px">
                                            @foreach (['open', 'acknowledged', 'investigating', 'resolved', 'closed'] as $status)
                                                <option value="{{ $status }}" @selected($incident->status === $status)>{{ ucfirst($status) }}</option>
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
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-exclamation-octagon"></i></span>
                                    No incidents match the filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $incidents->links() }}</div>
@endsection
