@extends('layouts.app')

@section('title', 'Search')

@push('styles')
    <style>
        .search-console-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Unified compound search bar with strict flex box containment */
        .search-compound-bar {
            background: #ffffff;
            border: 1px solid var(--cw-border, #cbd5e1);
            border-radius: 9999px;
            padding: 4px 6px 4px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.15s ease-out;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .search-compound-bar:focus-within {
            border-color: var(--cw-accent, #ff6b00);
            box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.15);
        }

        .search-scope-select {
            border: none;
            background: #f8fafc;
            color: #334155;
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 9999px;
            cursor: pointer;
            outline: none;
            flex-shrink: 0;
        }

        .search-scope-select:focus {
            background: #f1f5f9;
        }

        .search-input-field {
            border: none;
            outline: none;
            flex: 1 1 auto;
            min-width: 0; /* Crucial to prevent flex overflow */
            font-size: 0.9375rem;
            padding: 6px 4px;
            color: #0f172a;
            background: transparent;
        }

        .search-input-field::placeholder {
            color: #94a3b8;
            font-size: 0.875rem;
        }

        .search-kbd-hint {
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px 6px;
            user-select: none;
            flex-shrink: 0;
        }

        .search-submit-btn {
            flex-shrink: 0;
            border-radius: 9999px;
            padding: 6px 18px;
            font-size: 0.8125rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Filter chips row */
        .search-chips-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .search-chip {
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid var(--cw-border, #e2e8f0);
            background: #ffffff;
            color: #475569;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.12s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
        }

        .search-chip:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .search-chip.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        /* Property grouped card */
        .property-group-card {
            background: #ffffff;
            border: 1px solid var(--cw-border, #e2e8f0);
            border-radius: 6px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .property-group-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--cw-border, #e2e8f0);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .recent-searches-box {
            background: #ffffff;
            border: 1px solid var(--cw-border, #e2e8f0);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .recent-item-chip {
            font-family: var(--font-mono, monospace);
            font-size: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 4px;
            color: #334155;
            text-decoration: none;
            cursor: pointer;
        }

        .recent-item-chip:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
    </style>
@endpush

@section('content')
<div class="search-console-container">
    <!-- Breadcrumb Eyebrow only (Title & Subtitle removed per request) -->
    <div class="reveal" style="--d: 20ms">
        <span class="eyebrow"><i class="bi bi-search me-1"></i>Search · Operations Console</span>
    </div>

    <!-- Search Compound Control Card -->
    <div class="reveal" style="--d: 40ms">
        <form method="GET" action="{{ route('search') }}" id="search-console-form">
            <div class="search-compound-bar">
                <!-- Scope selector — Defaults to Property -->
                <select name="scope" id="search-scope" class="search-scope-select" aria-label="Search scope">
                    <option value="properties" @selected($scope === 'properties')>Properties & Tasks</option>
                    <option value="all" @selected($scope === 'all')>All Scopes</option>
                    <option value="tasks" @selected($scope === 'tasks')>Tasks Only</option>
                    @if(auth()->user()->hasPermission('2.1'))
                        <option value="personnel" @selected($scope === 'personnel')>Personnel</option>
                    @endif
                    @if(auth()->user()->hasPermission('3.1') && !auth()->user()->hasRole(2))
                        <option value="clients" @selected($scope === 'clients')>Clients</option>
                    @endif
                    @if(auth()->user()->hasPermission('8.1'))
                        <option value="incidents" @selected($scope === 'incidents')>Incidents</option>
                    @endif
                </select>

                <i class="bi bi-search text-muted fs-6" aria-hidden="true"></i>

                <!-- Unified Input Field -->
                <input type="search" name="q" id="search-input" value="{{ $q }}" 
                       class="search-input-field" 
                       placeholder="Search by property code, property name, address, client, task code…"
                       autocomplete="off" autofocus>

                <!-- Shortcut Badge -->
                <span class="search-kbd-hint d-none d-md-inline" id="kbd-shortcut-hint" title="Press / or Ctrl+K to search">/</span>

                @if($hasSearch)
                    <a href="{{ route('search') }}" class="btn btn-sm btn-link text-muted text-decoration-none p-1 flex-shrink-0" aria-label="Clear search" title="Clear">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif

                <!-- Inline Search Button (Contained inside pill) -->
                <button type="submit" class="btn btn-sm btn-primary search-submit-btn">
                    Search
                </button>
            </div>

            <!-- Scope & Filter Chips Row -->
            <div class="search-chips-row mt-2">
                <span class="extra-small mono text-muted me-1 text-uppercase">Scope:</span>
                <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'properties'])) }}" class="search-chip {{ $scope === 'properties' ? 'active' : '' }}">
                    <i class="bi bi-house-door extra-small"></i>Properties
                </a>
                <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'tasks'])) }}" class="search-chip {{ $scope === 'tasks' ? 'active' : '' }}">
                    <i class="bi bi-check2-square extra-small"></i>Tasks
                </a>
                <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'all'])) }}" class="search-chip {{ $scope === 'all' ? 'active' : '' }}">
                    <i class="bi bi-grid-fill extra-small"></i>All
                </a>
                @if(auth()->user()->hasPermission('2.1'))
                    <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'personnel'])) }}" class="search-chip {{ $scope === 'personnel' ? 'active' : '' }}">
                        <i class="bi bi-people extra-small"></i>Personnel
                    </a>
                @endif
                @if(auth()->user()->hasPermission('3.1') && !auth()->user()->hasRole(2))
                    <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'clients'])) }}" class="search-chip {{ $scope === 'clients' ? 'active' : '' }}">
                        <i class="bi bi-person-workspace extra-small"></i>Clients
                    </a>
                @endif
                @if(auth()->user()->hasPermission('8.1'))
                    <a href="{{ route('search', array_merge(request()->query(), ['scope' => 'incidents'])) }}" class="search-chip {{ $scope === 'incidents' ? 'active' : '' }}">
                        <i class="bi bi-exclamation-octagon extra-small"></i>Incidents
                    </a>
                @endif

                @if($hasSearch)
                    <div class="ms-auto">
                        <a href="{{ route('search') }}" class="extra-small text-danger text-decoration-none fw-semibold">
                            <i class="bi bi-x me-0.5"></i>Clear all
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Recent Searches Box -->
    <div id="recent-searches-container" class="recent-searches-box shadow-sm d-none reveal" style="--d: 50ms">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="mono extra-small text-muted text-uppercase"><i class="bi bi-clock-history me-1"></i>Recent Searches</span>
            <button type="button" class="btn btn-link text-muted p-0 extra-small text-decoration-none" id="btn-clear-recents">Clear</button>
        </div>
        <div class="d-flex flex-wrap gap-1.5" id="recent-searches-list"></div>
    </div>

    @if(! $hasSearch)
        <!-- Idle State / Empty Guidance -->
        <div class="card border-0 shadow-sm text-center py-5 bg-white rounded-3 reveal" style="--d: 60ms">
            <div class="card-body py-4">
                <div class="d-inline-flex p-3 rounded-circle bg-light mb-3 text-muted">
                    <i class="bi bi-search display-6"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Search properties and operational tasks</h5>
                <p class="text-muted small mb-3" style="max-width: 540px; margin: 0 auto;">
                    Enter a property code, property name, address, or task code to instantly view property details and ordered tasks.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <span class="badge bg-light text-secondary border mono">Property Code: PR-001</span>
                    <span class="badge bg-light text-secondary border mono">Property Name: Victoria</span>
                    <span class="badge bg-light text-secondary border mono">Address: Queen Street</span>
                    <span class="badge bg-light text-secondary border mono">Client: ACME Corp</span>
                </div>
            </div>
        </div>
    @elseif($totalResults === 0)
        <!-- No Results State -->
        <div class="card border-0 shadow-sm text-center py-5 bg-white rounded-3 reveal" style="--d: 60ms">
            <div class="card-body py-4">
                <div class="d-inline-flex p-3 rounded-circle bg-warning-subtle text-warning mb-3">
                    <i class="bi bi-question-circle display-6"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No matching properties or tasks found for "{{ $q ?: $propertyCode ?: $taskCode }}"</h5>
                <p class="text-muted small mb-3">
                    Check your property code or keywords, or try searching under "All Scopes".
                </p>
                <a href="{{ route('search') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Search
                </a>
            </div>
        </div>
    @else
        <!-- Results Summary Bar -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 reveal" style="--d: 50ms">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="mono fw-bold extra-small text-muted text-uppercase">Found {{ $totalResults }} result{{ $totalResults === 1 ? '' : 's' }}</span>
                @if($properties->isNotEmpty())
                    <a href="#section-properties" class="badge rounded-pill bg-light text-dark border text-decoration-none">
                        Properties ({{ $properties->count() }})
                    </a>
                @endif
                @if($tasks->isNotEmpty())
                    <a href="#section-tasks" class="badge rounded-pill bg-light text-dark border text-decoration-none">
                        Tasks ({{ $tasks->count() }})
                    </a>
                @endif
                @if($personnel->isNotEmpty())
                    <a href="#section-personnel" class="badge rounded-pill bg-light text-dark border text-decoration-none">
                        Personnel ({{ $personnel->count() }})
                    </a>
                @endif
                @if($clients->isNotEmpty())
                    <a href="#section-clients" class="badge rounded-pill bg-light text-dark border text-decoration-none">
                        Clients ({{ $clients->count() }})
                    </a>
                @endif
                @if($incidents->isNotEmpty())
                    <a href="#section-incidents" class="badge rounded-pill bg-light text-dark border text-decoration-none">
                        Incidents ({{ $incidents->count() }})
                    </a>
                @endif
            </div>
        </div>

        <!-- 1. Matching Properties List -->
        @if($properties->isNotEmpty())
            <div class="mb-4 reveal" id="section-properties" style="--d: 60ms">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="eyebrow"><i class="bi bi-house-door me-1"></i>Matching Properties ({{ $properties->count() }})</span>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Property</th>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Client</th>
                                    <th>Contact</th>
                                    <th class="text-center">Total Tasks</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($properties as $property)
                                    <tr>
                                        <td>
                                            <a href="{{ route('properties.edit', $property) }}" class="fw-bold text-dark text-decoration-none">
                                                @if($property->property_code)
                                                    <span class="badge bg-light text-secondary border mono extra-small me-1">[{{ $property->property_code }}]</span>
                                                @endif
                                                {{ $property->name }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $property->address ?: $property->formatted_address }}</small>
                                        </td>
                                        <td class="mono fw-semibold text-primary-emphasis">{{ $property->property_code ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $property->category?->name ?? 'Standard' }}</span>
                                        </td>
                                        <td>
                                            @if($property->client?->name || $property->client?->company_name)
                                                <div class="fw-semibold text-dark"><i class="bi bi-person me-1 text-muted"></i>{{ $property->client->name ?: $property->client->company_name }}</div>
                                            @else
                                                <span class="text-muted extra-small">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            {{ $property->contact_name ?: '—' }}
                                            @if($property->contact_phone)<br><span class="text-muted mono extra-small">{{ $property->contact_phone }}</span>@endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-bold mono">{{ $property->tasks_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('tasks', ['property_id' => $property->id, 'tab' => 'filters', 'apply' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="View all tasks in Register">
                                                    <i class="bi bi-list-task me-1"></i>Tasks
                                                </a>
                                                <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary" title="Edit Property">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. Tasks for Property (Ordered by Status, Date Time, Property) -->
        @if($tasks->isNotEmpty())
            <div class="mb-4 reveal" id="section-tasks" style="--d: 80ms">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="eyebrow"><i class="bi bi-check2-square me-1"></i>Tasks for Property ({{ $tasks->count() }}) — Ordered by Status & Date</span>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Task Title & Ref</th>
                                    <th>Property & Client</th>
                                    <th>Status</th>
                                    <th>Scheduled Date / Time</th>
                                    <th>Priority</th>
                                    <th>Assignees</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    @php
                                        $isMine = auth()->user()->hasRole(\App\Models\User::ROLE_CLEANER);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($task->taskType)
                                                <span class="badge bg-primary-subtle text-primary border-0 extra-small mb-1">{{ $task->taskType->name }}</span><br>
                                            @endif
                                            <a href="{{ $isMine ? route('tasks.work', $task) : route('tasks.edit', $task) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                            <br>
                                            <small class="text-muted mono">{{ $task->reference_number }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                @if($task->property?->property_code)
                                                    <span class="badge bg-light text-secondary border mono extra-small">[{{ $task->property->property_code }}]</span>
                                                @endif
                                                <span class="text-dark fw-semibold">{{ $task->property_name_snapshot ?? $task->property?->name ?? '—' }}</span>
                                            </div>
                                            @if($task->property?->address || $task->address_snapshot)
                                                <small class="text-muted d-block">{{ Str::limit($task->property?->address ?? $task->address_snapshot, 40) }}</small>
                                            @endif
                                            @if($task->property?->client?->name || $task->property?->client?->company_name)
                                                <small class="text-muted d-block extra-small"><i class="bi bi-person me-0.5"></i>Client: {{ $task->property->client->name ?: $task->property->client->company_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @include('partials.task-status-icon', ['task' => $task])
                                        </td>
                                        <td class="mono small">
                                            <div class="fw-semibold text-dark">
                                                {{ $task->scheduled_start_at ? $task->scheduled_start_at->format('M j, Y') : 'Unscheduled' }}
                                            </div>
                                            @if($task->scheduled_start_at)
                                                <small class="text-muted">{{ $task->scheduled_start_at->format('H:i') }} @if($task->scheduled_end_at) → {{ $task->scheduled_end_at->format('H:i') }} @endif</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($task->assignments as $asgn)
                                                    <span class="status-badge status-muted extra-small">{{ $asgn->assignee?->name ?? ('#'.$asgn->assignee_id) }}</span>
                                                @empty
                                                    <span class="text-muted extra-small">Unassigned</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ $isMine ? route('tasks.work', $task) : route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-{{ $isMine ? 'play-fill' : 'pencil' }} me-1"></i>{{ $isMine ? 'Work' : 'Open' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- 3. Personnel Section (If Searched) -->
        @if($personnel->isNotEmpty())
            <div class="mb-3 reveal" id="section-personnel" style="--d: 100ms">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="eyebrow"><i class="bi bi-people me-1"></i>Personnel ({{ $personnel->count() }})</span>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personnel as $person)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $person->name }}</td>
                                        <td>
                                            <span class="role-chip">@switch($person->role)
@case(0) Admin @break
@case(1) Supervisor @break
@default Cleaner @endswitch</span>
                                        </td>
                                        <td class="mono small">{{ $person->email }}</td>
                                        <td class="mono small">{{ $person->phone ?: '—' }}</td>
                                        <td class="text-end">
                                            @if(auth()->user()->hasPermission('2.1'))
                                                <a href="{{ route('personnel.edit', $person) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil me-1"></i>Manage
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- 4. Clients Section (If Searched) -->
        @if($clients->isNotEmpty())
            <div class="mb-3 reveal" id="section-clients" style="--d: 120ms">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="eyebrow"><i class="bi bi-person-workspace me-1"></i>Clients ({{ $clients->count() }})</span>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Client / Company</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $client->name }}</span>
                                            @if($client->company_name)<br><small class="text-muted">{{ $client->company_name }}</small>@endif
                                        </td>
                                        <td class="mono small">{{ $client->email ?: '—' }}</td>
                                        <td class="mono small">{{ $client->phone ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- 5. Incidents Section (If Searched) -->
        @if($incidents->isNotEmpty())
            <div class="mb-3 reveal" id="section-incidents" style="--d: 140ms">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="eyebrow"><i class="bi bi-exclamation-octagon me-1"></i>Incidents ({{ $incidents->count() }})</span>
                </div>
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.8125rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Incident</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incidents as $incident)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $incident->title }}</span><br>
                                            <small class="text-muted mono">{{ $incident->reference_number }}</small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $incident->severity === 'critical' ? 'danger' : 'warning' }}">
                                                {{ ucfirst($incident->severity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-muted">{{ ucfirst($incident->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var STORAGE_KEY = 'cleanway_recent_searches';

        // 1. Keyboard shortcut / or Cmd+K / Ctrl+K to focus search bar
        $(document).on('keydown', function (e) {
            if ((e.key === '/' || ((e.metaKey || e.ctrlKey) && e.key === 'k')) && !$('input, textarea, select').is(':focus')) {
                e.preventDefault();
                $('#search-input').focus().select();
            }
        });

        // 2. Recent Searches Storage & Display
        function getRecentSearches() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (err) {
                return [];
            }
        }

        function saveSearch(query) {
            if (!query || query.trim().length < 2) return;
            var q = query.trim();
            var list = getRecentSearches().filter(function (item) { return item !== q; });
            list.unshift(q);
            if (list.length > 5) list = list.slice(0, 5);
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
            } catch (e) {}
        }

        function renderRecentSearches() {
            var list = getRecentSearches();
            if (list.length === 0) {
                $('#recent-searches-container').addClass('d-none');
                return;
            }
            var $target = $('#recent-searches-list').empty();
            list.forEach(function (term) {
                var $chip = $('<a href="#" class="recent-item-chip"><i class="bi bi-search extra-small me-1 text-muted"></i>' + $('<div>').text(term).html() + '</a>');
                $chip.on('click', function (e) {
                    e.preventDefault();
                    $('#search-input').val(term);
                    $('#search-console-form').submit();
                });
                $target.append($chip);
            });
            $('#recent-searches-container').removeClass('d-none');
        }

        $('#btn-clear-recents').on('click', function () {
            localStorage.removeItem(STORAGE_KEY);
            $('#recent-searches-container').addClass('d-none');
        });

        // Save current search if executed
        @if($hasSearch && !empty($q))
            saveSearch('{{ addslashes($q) }}');
        @endif

        // Render on load if search is empty
        @if(!$hasSearch)
            renderRecentSearches();
        @endif
    })(jQuery);
</script>
@endpush
