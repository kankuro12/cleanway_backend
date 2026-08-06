@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Operations</span>
            <h2 class="h3 mt-1 mb-0">Task register</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('calendar') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Calendar
            </a>
            <a href="{{ route('recurrences') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Recurrences
            </a>
            @if(auth()->user()->hasPermission('4.2'))
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>New task
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

    <form method="GET" id="filter-form" class="filter-form mb-3 reveal" style="--d: 80ms" role="search">
        <div class="filter-sheet-head">
            <span class="mono text-muted">Filter options</span>
            <button type="button" class="btn btn-icon-touch" data-filter-close aria-label="Close filters">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="row g-2 filter-sheet-body">
        <div class="col-md-2">
            <label for="status" class="visually-hidden">Status</label>
            <select name="status" id="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                @foreach (['draft', 'scheduled', 'unassigned', 'assigned', 'accepted', 'declined', 'in_progress', 'paused', 'delayed', 'unable_to_access', 'completed', 'submitted_for_approval', 'correction_requested', 'rejected', 'reopened', 'approved', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="priority" class="visually-hidden">Priority</label>
            <select name="priority" id="priority" class="form-select form-select-sm">
                <option value="">All priorities</option>
                @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                    <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="task_type_id" class="visually-hidden">Task type</label>
            <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                <option value="">All types</option>
                @foreach ($taskTypes as $type)
                    <option value="{{ $type->id }}" @selected(request('task_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="property_id" class="visually-hidden">Property</label>
            <select name="property_id" id="property_id" class="form-select form-select-sm">
                <option value="">All properties</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}" @selected(request('property_id') == $property->id)>{{ $property->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="assignee_id" class="visually-hidden">Assignee</label>
            <select name="assignee_id" id="assignee_id" class="form-select form-select-sm">
                <option value="">All assignees</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}" @selected(request('assignee_id') == $assignee->id)>{{ $assignee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100 d-none d-md-block">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
        </div>
        </div>
        <div class="filter-sheet-foot">
            <button type="submit" class="btn btn-touch w-100">Apply filters</button>
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>When</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assignees</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td data-label="Task">
                                <span class="fw-semibold text-dark">{{ $task->title }}</span><br>
                                <small class="text-muted mono">{{ $task->reference_number }} · {{ $task->property_name_snapshot ?? 'One-off location' }}</small>
                            </td>
                            <td data-label="When">
                                {{ $task->scheduled_start_at?->format('D j M H:i') }}
                                @if($task->scheduled_end_at)<br><small class="text-muted">→ {{ $task->scheduled_end_at->format('H:i') }}</small>@endif
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $task->status === 'approved' || $task->status === 'completed' ? 'active' : (in_array($task->status, ['in_progress', 'accepted']) ? 'warning' : (in_array($task->status, ['cancelled', 'rejected']) ? 'danger' : 'muted')) }}">
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </td>
                            <td data-label="Priority"><span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">{{ $task->priority }}</span></td>
                            <td data-label="Assignees">
                                @forelse ($task->assignments as $assignment)
                                    <span class="status-badge status-muted">{{ $assignment->assignee_id }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td class="text-end" data-label="Actions">
                                <div class="d-flex gap-1 justify-content-end">
                                    @include('partials.directions-button', ['task' => $task])
                                    <a href="{{ auth()->user()->hasRole(\App\Models\User::ROLE_CLEANER) ? route('tasks.work', $task) : route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-{{ auth()->user()->hasRole(\App\Models\User::ROLE_CLEANER) ? 'play' : 'pencil' }} me-1" aria-hidden="true"></i>{{ auth()->user()->hasRole(\App\Models\User::ROLE_CLEANER) ? 'Work' : 'Open' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
                                    No tasks match the current filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $tasks->links() }}</div>
@endsection
