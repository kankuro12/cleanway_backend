@php
    $mine = $mine ?? false;
@endphp
<div class="table-responsive d-none d-lg-block">
    <table class="table table-hover align-middle mb-0">
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
                        @include('partials.task-status-icon', ['task' => $task])
                    </td>
                    <td data-label="Priority"><span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">{{ $task->priority }}</span></td>
                    <td data-label="Assignees">
                        @forelse ($task->assignments as $assignment)
                            <span class="status-badge status-muted">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
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
                            No tasks found.
                        </div>
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
        <div class="empty-state py-4">
            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
            No tasks found.
        </div>
    @endforelse
</div>
