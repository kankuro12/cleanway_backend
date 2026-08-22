@php
    $statusIcons = [
        'draft' => ['bi-file-earmark', 'muted'],
        'scheduled' => ['bi-calendar-check', 'warning'],
        'unassigned' => ['bi-person-x', 'muted'],
        'assigned' => ['bi-person-check', 'info'],
        'accepted' => ['bi-hand-thumbs-up', 'info'],
        'declined' => ['bi-hand-thumbs-down', 'danger'],
        'in_progress' => ['bi-play-circle-fill', 'warning'],
        'paused' => ['bi-pause-circle', 'muted'],
        'delayed' => ['bi-hourglass-split', 'warning'],
        'unable_to_access' => ['bi-x-octagon', 'danger'],
        'completed' => ['bi-check2-circle', 'active'],
        'submitted_for_approval' => ['bi-send', 'warning'],
        'correction_requested' => ['bi-arrow-repeat', 'warning'],
        'rejected' => ['bi-x-circle', 'danger'],
        'reopened' => ['bi-arrow-counterclockwise', 'info'],
        'approved' => ['bi-check-circle-fill', 'active'],
        'cancelled' => ['bi-slash-circle', 'muted'],
    ];
    [$icon, $color] = $statusIcons[$task->status] ?? ['bi-circle', 'muted'];
    $label = str_replace('_', ' ', $task->status);
@endphp
<i class="bi {{ $icon }} status-icon status-{{ $color }}" role="img" aria-label="{{ $label }}" title="{{ $label }}"></i>
