@php
    $simplified = $task->simplified_status ?? 'not_started';
    $statusMap = [
        'not_started' => ['icon' => 'bi-clock', 'color' => 'muted', 'label' => 'Not started'],
        'in_progress' => ['icon' => 'bi-play-circle-fill', 'color' => 'warning', 'label' => 'In progress'],
        'completed' => ['icon' => 'bi-check2-circle', 'color' => 'active', 'label' => 'Completed'],
        'cancelled' => ['icon' => 'bi-slash-circle', 'color' => 'danger', 'label' => 'Cancelled'],
    ];
    $statusData = $statusMap[$simplified] ?? $statusMap['not_started'];
@endphp
<span class="status-badge status-{{ $statusData['color'] }}" title="{{ $statusData['label'] }}">
    <i class="bi {{ $statusData['icon'] }} me-1" aria-hidden="true"></i>{{ $statusData['label'] }}
</span>
