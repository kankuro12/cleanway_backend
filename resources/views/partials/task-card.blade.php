@php
    $mine = $mine ?? false;
    $isSupervisor = ! auth()->user()?->hasRole(\App\Models\User::ROLE_CLEANER);
    $isNotStarted = in_array($task->status, ['not_started', 'draft', 'scheduled', 'unassigned', 'assigned', 'accepted'], true);
@endphp
<div class="task-card-item" id="task-card-{{ $task->id }}">
    <div class="task-card-head">
        <a href="{{ route('tasks.work', $task) }}" class="min-w-0 flex-grow-1 text-decoration-none text-reset d-block" title="Open task work page">
            <div class="task-card-title fw-bold">
                @if($task->property?->property_code)
                    <span class="badge bg-light text-secondary border mono extra-small me-1">[{{ $task->property->property_code }}]</span>
                @endif
                {{ $task->property_name_snapshot ?? $task->property?->name ?? 'Task' }}
            </div>
            <div class="task-card-address text-muted"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $task->property?->address ?? 'One-off location' }}</div>
            @if($task->property?->client?->name || $task->property?->client?->company_name)
                <div class="extra-small text-muted"><i class="bi bi-person me-1"></i>Client: {{ $task->property->client->name ?: $task->property->client->company_name }}</div>
            @endif
        </a>
        @include('partials.task-status-icon', ['task' => $task])
    </div>
    <div class="task-card-body">
        <div class="task-card-meta">
            @if($isSupervisor && $isNotStarted)
                <button type="button" class="btn btn-link text-decoration-none p-0 text-start js-quick-schedule-trigger text-dark"
                        data-task-id="{{ $task->id }}"
                        data-task-title="{{ $task->title }}"
                        data-start="{{ $task->scheduled_start_at?->format('Y-m-d\TH:i') }}"
                        data-end="{{ $task->scheduled_end_at?->format('Y-m-d\TH:i') }}">
                    <i class="bi bi-calendar3 me-1" aria-hidden="true"></i><span class="border-bottom border-dotted text-primary-emphasis">{{ $task->scheduled_start_at?->format('D j M H:i') ?? 'Set Date' }}</span>
                </button>
            @else
                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ $task->scheduled_start_at?->format('D j M H:i') ?? '—' }}
            @endif
        </div>
        <a href="{{ route('tasks.work', $task) }}" class="text-decoration-none text-reset d-inline-block" title="Open task work page">
            <div class="task-card-type fw-semibold">{{ $task->taskType?->name ?? $task->title }}</div>
        </a>
        <div class="task-card-assignees mt-1">
            @if($isSupervisor && $isNotStarted)
                <button type="button" class="btn btn-link text-decoration-none p-0 text-start js-quick-assign-trigger d-flex flex-wrap gap-1 align-items-center"
                        data-task-id="{{ $task->id }}"
                        data-task-title="{{ $task->title }}"
                        data-assigned-ids="{{ json_encode($task->assignments->where('assignee_type', 'user')->pluck('assignee_id')->all()) }}">
                    <i class="bi bi-people me-1" aria-hidden="true"></i>
                    <span class="d-flex flex-wrap gap-1 align-items-center">
                        @forelse ($task->assignments as $assignment)
                            <span class="status-badge status-muted">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                        @empty
                            <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-person-plus me-1"></i>Assign</span>
                        @endforelse
                    </span>
                </button>
            @else
                <i class="bi bi-people me-1" aria-hidden="true"></i>
                @forelse ($task->assignments as $assignment)
                    <span class="status-badge status-muted">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                @empty
                    <span class="text-muted extra-small">Unassigned</span>
                @endforelse
            @endif
        </div>
    </div>
    <div class="task-card-footer">
        @if($task->latitude_snapshot && $task->longitude_snapshot)
            <a href="https://www.google.com/maps?q={{ $task->latitude_snapshot }},{{ $task->longitude_snapshot }}" target="_blank" rel="noopener" class="icon-btn" aria-label="Directions to {{ $task->property_name_snapshot ?? 'task' }}">
                <i class="bi bi-sign-turn-right" aria-hidden="true"></i>
            </a>
        @endif
        @unless($mine)
            <a href="{{ route('tasks.edit', $task) }}" class="icon-btn" aria-label="Edit task">
                <i class="bi bi-pencil" aria-hidden="true"></i>
            </a>
        @endunless
        <a href="{{ route('tasks.work', $task) }}" class="icon-btn" aria-label="Task details and comments">
            <i class="bi bi-chat-left-text" aria-hidden="true"></i>
        </a>
        @if($mine)
            <a href="{{ route('tasks.work', $task) }}" class="icon-btn primary ms-auto" aria-label="Start task">
                <i class="bi bi-play-circle-fill" aria-hidden="true"></i>
            </a>
        @endif
    </div>
</div>
