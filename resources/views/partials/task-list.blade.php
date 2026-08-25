@php
    $mine = $mine ?? false;
    $isSupervisor = ! auth()->user()?->hasRole(\App\Models\User::ROLE_CLEANER);
@endphp
<div class="table-responsive d-none d-lg-block">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Task</th>
                <th>Property</th>
                <th>When</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Assignees</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
                @php
                    $isNotStarted = in_array($task->status, ['not_started', 'draft', 'scheduled', 'unassigned', 'assigned', 'accepted'], true);
                @endphp
                <tr id="task-row-{{ $task->id }}">
                    <td data-label="Task">
                        <span class="fw-semibold text-dark">{{ $task->title }}</span><br>
                        <small class="text-muted mono">{{ $task->reference_number }}</small>
                        @if($task->taskType)
                            <span class="badge bg-primary-subtle text-primary border-0 extra-small ms-1">{{ $task->taskType->name }}</span>
                        @endif
                    </td>
                    <td data-label="Property">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            @if($task->property?->property_code)
                                <span class="badge bg-light text-secondary border mono extra-small">[{{ $task->property->property_code }}]</span>
                            @endif
                            <span class="text-dark fw-semibold">{{ $task->property_name_snapshot ?? $task->property?->name ?? 'One-off location' }}</span>
                        </div>
                        @if($task->property?->address || $task->address_snapshot)
                            <small class="text-muted d-block">{{ Str::limit($task->property?->address ?? $task->address_snapshot, 36) }}</small>
                        @endif
                        @if($task->property?->client?->name || $task->property?->client?->company_name)
                            <small class="text-muted d-block extra-small"><i class="bi bi-person me-0.5"></i>Client: {{ $task->property->client->name ?: $task->property->client->company_name }}</small>
                        @endif
                    </td>
                    <td data-label="When">
                        @if($isSupervisor && $isNotStarted)
                            <button type="button" class="btn btn-link text-decoration-none p-0 text-start js-quick-schedule-trigger text-dark"
                                    data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}"
                                    data-start="{{ $task->scheduled_start_at?->format('Y-m-d\TH:i') }}"
                                    data-end="{{ $task->scheduled_end_at?->format('Y-m-d\TH:i') }}"
                                    title="Click to edit start date & time">
                                <span class="fw-medium text-primary-emphasis text-decoration-underline" id="task-schedule-text-{{ $task->id }}">
                                    {{ $task->scheduled_start_at?->format('D j M H:i') ?? 'Set Date' }}
                                    @if($task->scheduled_end_at)<br><small class="text-muted">→ {{ $task->scheduled_end_at->format('H:i') }}</small>@endif
                                </span>
                                <i class="bi bi-pencil-square text-muted extra-small ms-1"></i>
                            </button>
                        @else
                            {{ $task->scheduled_start_at?->format('D j M H:i') ?? '—' }}
                            @if($task->scheduled_end_at)<br><small class="text-muted">→ {{ $task->scheduled_end_at->format('H:i') }}</small>@endif
                        @endif
                    </td>
                    <td data-label="Status">
                        @include('partials.task-status-icon', ['task' => $task])
                    </td>
                    <td data-label="Priority"><span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">{{ $task->priority }}</span></td>
                    <td data-label="Assignees">
                        @if($isSupervisor && $isNotStarted)
                            <button type="button" class="btn btn-link text-decoration-none p-0 text-start js-quick-assign-trigger d-flex flex-wrap gap-1 align-items-center"
                                    data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}"
                                    data-assigned-ids="{{ json_encode($task->assignments->where('assignee_type', 'user')->pluck('assignee_id')->all()) }}"
                                    title="Click to assign personnel">
                                <span class="d-flex flex-wrap gap-1 align-items-center" id="task-assignees-badges-{{ $task->id }}">
                                    @forelse ($task->assignments as $assignment)
                                        <span class="status-badge status-muted">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                                    @empty
                                        <span class="badge bg-secondary-subtle text-secondary border border-dashed"><i class="bi bi-person-plus me-1"></i>Assign</span>
                                    @endforelse
                                </span>
                                <i class="bi bi-pencil-square text-muted extra-small ms-1"></i>
                            </button>
                        @else
                            @forelse ($task->assignments as $assignment)
                                <span class="status-badge status-muted">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                            @empty
                                —
                            @endforelse
                        @endif
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
                    <td colspan="7">
                        @if(request('tab') === 'filters' && !request('apply') && !request()->hasAny(['status', 'priority', 'task_type_id', 'property_id', 'assignee_id']))
                            <div class="empty-state py-5">
                                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-funnel"></i></span>
                                <div class="fw-semibold text-dark mb-1">Set filters and click "Filter" to search</div>
                                <small class="text-muted">Choose your date range, property, status, or assignee above to load tasks.</small>
                            </div>
                        @else
                            <div class="empty-state">
                                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
                                No tasks found.
                            </div>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-lg-none p-2 d-flex flex-column gap-2">
    @forelse ($tasks as $task)
        @include('partials.task-card', ['task' => $task, 'mine' => $mine])
    @empty
        @if(request('tab') === 'filters' && !request('apply') && !request()->hasAny(['status', 'priority', 'task_type_id', 'property_id', 'assignee_id']))
            <div class="empty-state py-5">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-funnel"></i></span>
                <div class="fw-semibold text-dark mb-1">Set filters and click "Filter" to search</div>
                <small class="text-muted">Choose your date range, property, status, or assignee above to load tasks.</small>
            </div>
        @else
            <div class="empty-state py-4">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
                No tasks found.
            </div>
        @endif
    @endforelse
</div>
