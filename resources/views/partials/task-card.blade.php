@php
    $mine = $mine ?? false;
    $assignees = $task->assignments->filter(fn ($a) => $a->assignee)->pluck('assignee.name')->implode(', ');
@endphp
<div class="task-card-item">
    <div class="task-card-head">
        <div class="min-w-0">
            <div class="task-card-title">{{ $task->property_name_snapshot ?? $task->property?->name ?? 'Task' }}</div>
            <div class="task-card-address"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $task->property?->address ?? 'One-off location' }}</div>
        </div>
        @include('partials.task-status-icon', ['task' => $task])
    </div>
    <div class="task-card-body">
        <div class="task-card-meta"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ $task->scheduled_start_at?->format('D j M H:i') }}</div>
        <div class="task-card-type">{{ $task->taskType?->name ?? $task->title }}</div>
        @if($assignees)
            <div class="task-card-assignees"><i class="bi bi-people me-1" aria-hidden="true"></i>{{ $assignees }}</div>
        @endif
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
