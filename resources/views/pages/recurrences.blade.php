@extends('layouts.app')

@section('title', 'Recurrences')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Scheduling</span>
            <h2 class="h3 mt-1 mb-0">Recurring task templates</h2>
        </div>
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Task register
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header mono">New template</div>
        <div class="card-body">
            <form method="POST" action="{{ route('recurrences.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label for="rule" class="form-label visually-hidden">Rule</label>
                    <input type="text" id="rule" name="rule" value="{{ old('rule', 'FREQ=WEEKLY;INTERVAL=1') }}" class="form-control form-control-sm" placeholder="FREQ=WEEKLY;INTERVAL=1" required>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label visually-hidden">Start</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label visually-hidden">End</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <label for="time" class="form-label visually-hidden">Time</label>
                    <input type="time" id="time" name="time" value="{{ old('time', '08:00') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label for="task_type_id" class="form-label visually-hidden">Task type</label>
                    <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                        <option value="">Task type</option>
                        @foreach ($taskTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus me-1" aria-hidden="true"></i>Add template
                    </button>
                </div>
                <div class="col-md-3">
                    <label for="property_id" class="form-label visually-hidden">Property</label>
                    <select name="property_id" id="property_id" class="form-select form-select-sm">
                        <option value="">Property</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="assignee_type" class="form-label visually-hidden">Assignee type</label>
                    <select name="assignee_type" id="assignee_type" class="form-select form-select-sm">
                        <option value="">No default assignee</option>
                        <option value="user">Person</option>
                        <option value="team">Team</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="assignee_id" class="form-label visually-hidden">Assignee ID</label>
                    <select name="assignee_id" id="assignee_id" class="form-select form-select-sm">
                        <option value="">Default assignee</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="checklist_template_id" class="form-label visually-hidden">Checklist</label>
                    <select name="checklist_template_id" id="checklist_template_id" class="form-select form-select-sm">
                        <option value="">Checklist template</option>
                        @foreach ($checklists as $checklist)
                            <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Window</th>
                        <th>Time</th>
                        <th>Property</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recurrences as $recurrence)
                        <tr>
                            <td class="mono small">{{ $recurrence->rule }}</td>
                            <td class="small">{{ $recurrence->start_date->toDateString() }} → {{ $recurrence->end_date?->toDateString() ?? '∞' }}</td>
                            <td class="small">{{ $recurrence->time }}</td>
                            <td>{{ $recurrence->property?->name ?? '—' }}</td>
                            <td>{{ $recurrence->taskType?->name ?? '—' }}</td>
                            <td><span class="status-badge status-{{ $recurrence->active ? 'active' : 'muted' }}">{{ $recurrence->active ? 'active' : 'inactive' }}</span></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('recurrences.generate-now', $recurrence) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Generate instances now">
                                        <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Generate
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('recurrences.destroy', $recurrence) }}" class="d-inline" onsubmit="return confirm('Remove this recurrence template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0 ms-1" aria-label="Remove">
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                                    No recurrence templates yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $recurrences->links() }}</div>
@endsection
