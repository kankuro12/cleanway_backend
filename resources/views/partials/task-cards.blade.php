<!-- Main Group / Current Selection Section Band Header -->
<div class="section-band-header">
    @if($tab === 'history' || $tab === 'finished')
        <i class="bi bi-clock-history me-1"></i>COMPLETED TASK HISTORY
    @elseif($tab === 'tomorrow')
        <i class="bi bi-calendar-event me-1"></i>TOMORROW'S SCHEDULE
    @elseif($tab === 'week')
        <i class="bi bi-calendar-week me-1"></i>THIS WEEK'S TASKS
    @elseif($tab === 'all')
        <i class="bi bi-list-task me-1"></i>ALL ACTIVE TASKS
    @else
        <i class="bi bi-sun me-1 text-warning"></i>TODAY & OVERDUE TASKS
    @endif
</div>

<!-- Task Cards Loop -->
@forelse ($tasks as $task)
    @include('partials.task-card', ['task' => $task, 'mine' => true])
@empty
    <div class="card shadow-sm border-0 my-4 text-center py-5 bg-white reveal">
        <div class="card-body">
            <i class="bi bi-clipboard-check text-muted display-4 d-block mb-3"></i>
            <h5 class="fw-bold text-dark">No tasks found</h5>
            <p class="text-muted small mb-0">
                @if($tab === 'history' || $tab === 'finished')
                    You don't have any completed tasks in your history yet.
                @else
                    No active tasks found for {{ strtoupper($tab) }}. Check back later or view Task History.
                @endif
            </p>
        </div>
    </div>
@endforelse

<!-- End of List Indicator -->
<div class="text-center py-4 text-muted small mono reveal">
    You've reached the end of your list.
</div>
