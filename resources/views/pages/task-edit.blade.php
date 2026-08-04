@extends('layouts.app')

@section('title', $task->title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · {{ $task->reference_number }}</span>
            <h2 class="h3 mt-1 mb-0">{{ $task->title }}</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Register
            </a>
            @if(auth()->user()->hasPermission('4.6'))
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-1" aria-hidden="true"></i>Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-3 reveal" style="--d: 80ms">
                <div class="card-header mono">Details</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" value="{{ $task->title }}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select">
                                    @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                        <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estimated_duration_minutes" class="form-label">Duration (min)</label>
                                <input type="number" min="1" id="estimated_duration_minutes" name="estimated_duration_minutes" value="{{ $task->estimated_duration_minutes }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="scheduled_start_at" class="form-label">Starts at</label>
                                <input type="datetime-local" id="scheduled_start_at" name="scheduled_start_at"
                                       value="{{ $task->scheduled_start_at?->format('Y-m-d\TH:i') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="scheduled_end_at" class="form-label">Ends at</label>
                                <input type="datetime-local" id="scheduled_end_at" name="scheduled_end_at"
                                       value="{{ $task->scheduled_end_at?->format('Y-m-d\TH:i') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control">{{ $task->description }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-primary">
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save schedule & details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($task->checklistSnapshot->isNotEmpty())
                <div class="card shadow-sm mb-3 reveal" style="--d: 120ms">
                    <div class="card-header mono">Checklist snapshot (immutable)</div>
                    <div class="card-body">
                        @foreach ($task->checklistSnapshot->groupBy('section_name') as $section => $items)
                            <div class="fw-semibold small text-uppercase text-muted mb-1">{{ $section }}</div>
                            <ul class="list-unstyled ms-2 mb-3">
                                @foreach ($items as $item)
                                    <li class="small">
                                        <i class="bi bi-{{ $item->item_type === 'photo' ? 'camera' : ($item->item_type === 'text' ? 'font' : ($item->item_type === 'numeric' ? '123' : 'check-circle')) }} me-1" aria-hidden="true"></i>
                                        {{ $item->item_label }}
                                        @if($item->required)<span class="text-danger">*</span>@endif
                                        <span class="status-badge status-muted ms-1">{{ str_replace('_', ' ', $item->item_type) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card shadow-sm mb-3 reveal" style="--d: 140ms">
                <div class="card-header mono">Sub tasks</div>
                <div class="card-body">
                    @forelse ($task->subtasks as $subtask)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="small {{ $subtask->completed_at ? 'text-decoration-line-through text-muted' : '' }}">
                                @if($subtask->completed_at)<i class="bi bi-check2-circle text-success me-1" aria-hidden="true"></i>@else<i class="bi bi-circle me-1 text-muted" aria-hidden="true"></i>@endif
                                {{ $subtask->title }}
                            </span>
                            @if(auth()->user()->hasPermission('4.4'))
                                <form method="POST" action="{{ route('tasks.subtasks.toggle', [$task, $subtask]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-link p-0 {{ $subtask->completed_at ? 'text-muted' : 'text-success' }}">
                                        {{ $subtask->completed_at ? 'Reopen' : 'Done' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-2">No sub tasks.</p>
                    @endforelse

                    <form method="POST" action="{{ route('tasks.subtasks.store', $task) }}" class="mt-2">
                        @csrf
                        <div class="d-flex gap-2">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Add a sub task…">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-plus me-1" aria-hidden="true"></i>Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-3 reveal" style="--d: 160ms">
                <div class="card-header mono">Status history</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>From</th><th>To</th><th>By</th><th>When</th><th>Remarks</th></tr></thead>
                        <tbody>
                        @forelse ($task->history as $entry)
                            <tr>
                                <td class="small">{{ str_replace('_', ' ', $entry->previous_status ?? '—') }}</td>
                                <td class="small"><span class="status-badge status-muted">{{ str_replace('_', ' ', $entry->new_status) }}</span></td>
                                <td class="small">{{ $entry->user?->name ?? 'system' }}</td>
                                <td class="small text-muted">{{ $entry->created_at?->format('j M H:i') }}</td>
                                <td class="small text-muted">{{ $entry->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted small">No transitions yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-3 reveal" style="--d: 100ms">
                <div class="card-header mono">Status · {{ str_replace('_', ' ', $task->status) }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.transition', $task) }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label for="status" class="form-label visually-hidden">New status</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                @foreach ($task->transitionableStatuses() as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label visually-hidden">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i>Move status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-3 reveal" style="--d: 140ms">
                <div class="card-header mono">Assignments</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.assign', $task) }}" class="row g-2">
                        @csrf
                        <div class="col-6">
                            <label for="assignee_type" class="form-label visually-hidden">Type</label>
                            <select name="assignee_type" id="assignee_type" class="form-select form-select-sm">
                                <option value="user">Person</option>
                                <option value="team">Team</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="assignee_id" class="form-label visually-hidden">Assignee</label>
                            <input type="number" name="assignee_id" id="assignee_id" class="form-control form-control-sm" placeholder="ID" required min="1">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch d-inline-block me-2">
                                <input class="form-check-input" type="checkbox" name="override_warnings" value="1" id="override_warnings">
                                <label class="form-check-label small" for="override_warnings">Override warnings</label>
                            </div>
                            <input type="text" name="override_reason" class="form-control form-control-sm mt-2" placeholder="Override reason">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add assignee
                            </button>
                        </div>
                    </form>
                    <hr>
                    @forelse ($task->assignments as $assignment)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <div>
                                <span class="fw-semibold small">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                                <span class="status-badge status-muted ms-2">{{ $assignment->assignee_type }}</span>
                                <span class="status-badge status-{{ $assignment->status === 'accepted' ? 'active' : 'muted' }}">{{ $assignment->status }}</span>
                            </div>
                            <form method="POST" action="{{ route('tasks.unassign', [$task, $assignment]) }}" onsubmit="return confirm('Remove assignment?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0" aria-label="Remove assignment">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Not assigned yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm mb-3 reveal" style="--d: 180ms">
                <div class="card-header mono">Snapshot</div>
                <ul class="list-unstyled small mb-0 p-3">
                    <li><span class="text-muted">Location:</span> {{ $task->property_name_snapshot ?? '—' }}</li>
                    <li><span class="text-muted">Address:</span> {{ $task->address_snapshot ?? '—' }}</li>
                    <li><span class="text-muted">Radius:</span> {{ $task->check_in_radius_snapshot ? $task->check_in_radius_snapshot.' m' : '—' }}</li>
                    <li><span class="text-muted">Approval:</span> {{ $task->approval_required ? 'required' : 'not required' }}</li>
                    <li><span class="text-muted">Recurrence:</span> {{ $task->recurrence_rule ?? 'none' }}</li>
                </ul>
            </div>

            @if(auth()->user()->hasPermission('4.4'))
                <div class="card shadow-sm mb-3 reveal" style="--d: 200ms">
                    <div class="card-header mono">Evidence upload</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tasks.evidence', $task) }}" enctype="multipart/form-data" class="row g-2">
                            @csrf
                            <div class="col-7">
                                <label for="evidence_type" class="form-label visually-hidden">Type</label>
                                <select name="evidence_type" id="evidence_type" class="form-select form-select-sm">
                                    @foreach (['before', 'during', 'after', 'issue', 'safety', 'access_problem', 'other'] as $type)
                                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <label for="captured_at" class="form-label visually-hidden">Captured</label>
                                <input type="datetime-local" name="captured_at" id="captured_at" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label for="evidence" class="form-label visually-hidden">File</label>
                                <input type="file" name="evidence" id="evidence" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="bi bi-camera me-1" aria-hidden="true"></i>Upload evidence
                                </button>
                            </div>
                        </form>

                        @if($task->evidence()->exists())
                            <hr>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($task->evidence as $item)
                                    <span class="status-badge status-muted">{{ $item->evidence_type }} #{{ $item->id }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
