@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Operational Command Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 reveal">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="status-live-pulse"></span>
                <span class="eyebrow">FIELD OPERATIONS COMMAND</span>
            </div>
            <h1 class="h3 mt-1 mb-0 font-weight-bold">Today's Shift Dashboard</h1>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="badge bg-dark text-light border border-secondary p-2 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-warning"></i>
                <span class="mono" data-clock></span> UTC
            </div>
            <span class="role-chip"><i class="bi bi-calendar2-check me-1"></i>Shift {{ now()->format('D, d M Y') }}</span>
        </div>
    </div>

    <!-- Active Shift Progress Rail -->
    @php
        $totalToday = count($widgets['today']);
        $completedToday = $widgets['today']->where('status', 'completed')->count();
        $progressPct = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;
    @endphp
    <div class="shift-progress-box mb-4 reveal" style="--d: 60ms">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="mono small font-weight-bold text-uppercase">
                <i class="bi bi-activity text-accent me-1"></i>Shift Execution Progress
            </span>
            <span class="mono small font-weight-bold">{{ $completedToday }} of {{ $totalToday }} Tasks Completed ({{ $progressPct }}%)</span>
        </div>
        <div class="progress-rail">
            <div class="progress-rail-fill" style="width: {{ $progressPct }}%;"></div>
        </div>
    </div>

    <!-- Interactive Bento Operations Hub (Equal Height Stat Cards) -->
    <div class="row g-2 g-md-3 mb-4 align-items-stretch">
        @foreach ($widgets['stats'] as $i => $stat)
            @php
                $themeClass = match($stat['icon'] ?? '') {
                    'exclamation-triangle', 'exclamation-octagon' => 'stat-theme-danger',
                    'hourglass-split' => 'stat-theme-warning',
                    'clipboard-check', 'calendar-day' => 'stat-theme-accent',
                    'people', 'building' => 'stat-theme-info',
                    'geo-alt' => 'stat-theme-orange',
                    default => 'stat-theme-accent'
                };
            @endphp
            <div class="col-6 col-md-4 col-xl-3 d-flex align-items-stretch">
                @if(!empty($stat['url']))
                    <a href="{{ $stat['url'] }}" class="stat-card stat-card-link reveal {{ $themeClass }}" style="--d: {{ $i * 40 + 100 }}ms" @if(!empty($stat['filter'])) data-filter="{{ $stat['filter'] }}" @endif>
                @else
                    <div class="stat-card reveal {{ $themeClass }}" style="--d: {{ $i * 40 + 100 }}ms">
                @endif
                    <div class="stat-card-inner">
                        <div class="stat-card-top">
                            <div class="stat-card-value">{{ $stat['value'] }}</div>
                            <div class="stat-icon-wrapper">
                                <i class="bi bi-{{ $stat['icon'] }}" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="stat-card-label">{{ $stat['label'] }}</div>
                    </div>
                @if(!empty($stat['url']))
                    </a>
                @else
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Active Next Task Mission Banner -->
    @if(isset($widgets['next']) && $widgets['next'])
        @php $next = $widgets['next']; @endphp
        <div class="hazard-bar mb-4 reveal" style="--d: 220ms" role="alert">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 w-100">
                <div class="me-auto">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-dark text-warning mono fw-bold"><i class="bi bi-lightning-fill me-1"></i>ACTIVE MISSION</span>
                        <span class="mono small text-dark font-weight-bold">TSK-{{ $next->reference_number }}</span>
                    </div>
                    <h2 class="hazard-title mb-1 font-weight-bold">{{ $next->title }}</h2>
                    @if($next->property_name_snapshot)
                        <div class="small fw-semibold text-dark">
                            <i class="bi bi-building me-1"></i>{{ $next->property_name_snapshot }}
                            @if($next->scheduled_start_at)
                                · <i class="bi bi-clock me-1 ms-1"></i>Scheduled: {{ $next->scheduled_start_at->format('H:i') }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 w-100 w-md-auto flex-wrap">
                    @if(auth()->user()?->hasPermission('4.4'))
                        <a href="{{ route('tasks.work', $next) }}" class="btn btn-touch fw-bold me-1">
                            <i class="bi bi-play-circle-fill me-1"></i>Start Work Now
                        </a>
                    @else
                        <a href="{{ route('tasks.edit', $next) }}" class="btn btn-touch fw-bold me-1">
                            <i class="bi bi-folder2-open me-1"></i>Open Task
                        </a>
                    @endif
                    @if($next->latitude_snapshot && $next->longitude_snapshot)
                        <a href="https://www.google.com/maps?q={{ $next->latitude_snapshot }},{{ $next->longitude_snapshot }}" target="_blank" rel="noopener" class="btn btn-touch">
                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i>Navigate
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Interactive Filter Pills Bar -->
    <div class="filter-pills mb-3 reveal" style="--d: 260ms" id="dashboard-filter-pills">
        <button type="button" class="pill active" data-filter="all">All Tasks ({{ count($widgets['today']) }})</button>
        <button type="button" class="pill" data-filter="in_progress">In Progress</button>
        <button type="button" class="pill" data-filter="assigned,scheduled">Scheduled / Assigned</button>
        <button type="button" class="pill" data-filter="completed,approved">Completed</button>
    </div>

    <!-- Main Operational Grid Split: 7 Cols Tasks Today / 5 Cols Attention Items -->
    <div class="row g-4">
        <!-- Left Panel: Scheduled Tasks Stream -->
        <div class="col-lg-7">
            <div class="card reveal h-100" style="--d: 300ms">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-task me-2"></i>Scheduled Operations</span>
                    <span class="badge bg-secondary rounded-pill">{{ count($widgets['today']) }}</span>
                </div>

                <!-- High-Density Mobile Compact Stream (<992px) -->
                <div class="d-lg-none p-3 d-flex flex-column gap-2" id="mobile-tasks-container">
                    @forelse ($widgets['today'] as $task)
                        @php
                            $border = in_array($task->status, ['assigned', 'accepted', 'scheduled']) ? 'mtc-b-assigned'
                                : ($task->status === 'in_progress' ? 'mtc-b-in_progress'
                                : (in_array($task->status, ['completed', 'approved']) ? 'mtc-b-completed' : 'mtc-b-muted'));
                        @endphp
                        <div class="mobile-task-card compact {{ $border }} js-feed-card" data-status="{{ $task->status }}">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <span class="mtc-ref">{{ $task->reference_number }}</span>
                                <span class="status-badge status-muted">{{ str_replace('_', ' ', $task->status) }}</span>
                            </div>
                            <div class="mtc-title">{{ $task->title }}</div>
                            <div class="mtc-meta mt-1">
                                <i class="bi bi-clock me-1" aria-hidden="true"></i>{{ $task->scheduled_start_at?->format('H:i') }}
                                @if($task->property_name_snapshot)
                                    <span class="ms-1">· <i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $task->property_name_snapshot }}</span>
                                @endif
                                @if($task->assignments->isNotEmpty())
                                    · @foreach ($task->assignments as $a){{ $a->assignee?->name ?? '#' . $a->assignee_id }}@if(!$loop->last), @endif @endforeach
                                @endif
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                @if(auth()->user()->hasPermission('4.4'))
                                    <a href="{{ route('tasks.work', $task) }}" class="btn btn-touch flex-fill">Start work</a>
                                @endif
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary btn-touch flex-fill">View</a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-4">
                            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-inbox"></i></span>
                            Nothing scheduled today.
                        </div>
                    @endforelse
                    <div class="empty-state py-4 d-none" id="feed-empty">
                        <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-funnel"></i></span>
                        No tasks match this filter.
                    </div>
                </div>

                <!-- Desktop Command Table (>=992px) -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Task Details</th>
                                <th>Assignees</th>
                                <th>Status</th>
                                <th>Scheduled</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($widgets['today'] as $task)
                                <tr class="js-feed-card" data-status="{{ $task->status }}">
                                    <td class="mono small font-weight-bold">{{ $task->reference_number }}</td>
                                    <td>
                                        <div class="fw-semibold text-strong">{{ $task->title }}</div>
                                        @if($task->property_name_snapshot)
                                            <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $task->property_name_snapshot }}</small>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($task->assignments->isNotEmpty())
                                            @foreach ($task->assignments as $a){{ $a->assignee?->name ?? '#' . $a->assignee_id }}@if(!$loop->last), @endif @endforeach
                                        @else
                                            <span class="fst-italic text-faint">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $task->status === 'in_progress' ? 'warning' : ($task->status === 'completed' ? 'active' : 'muted') }}">
                                            {{ str_replace('_', ' ', $task->status) }}
                                        </span>
                                    </td>
                                    <td class="mono small">{{ $task->scheduled_start_at?->format('H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary btn-sm">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state py-4">
                                            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-inbox"></i></span>
                                            Nothing scheduled today.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Panel: Real-Time Needs Attention Stream -->
        <div class="col-lg-5">
            @foreach ($widgets['attention'] as $section => $items)
                <div class="card reveal mb-3" style="--d: 340ms">
                    <div class="card-header mono d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-exclamation-octagon text-danger me-2"></i>{{ $section }}</span>
                        <span class="badge bg-danger rounded-pill">{{ count($items) }}</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse ($items as $item)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="small flex-grow-1">
                                        @if(isset($item->event))
                                            <span class="status-badge status-warning me-1">gps</span>
                                            <strong class="text-strong">{{ $item->event?->user?->name }}</strong> — {{ $item->reason ?? 'GPS exception' }}
                                        @elseif(isset($item->reporter))
                                            <strong class="d-block text-strong">{{ $item->description }}</strong>
                                            <span class="status-badge status-{{ $item->severity === 'critical' ? 'danger' : ($item->severity === 'high' ? 'warning' : 'info') }} mt-1">
                                                {{ $item->severity }}
                                            </span>
                                        @else
                                            <span class="status-badge status-info me-1">pending</span>
                                            <span class="text-strong">{{ $item->reason ?? $item->title ?? '—' }}</span>
                                        @endif
                                    </div>
                                    @if(isset($item->reporter))
                                        <a href="{{ route('incidents') }}" class="btn btn-outline-secondary btn-touch">Review</a>
                                    @elseif(isset($item->originalEvent))
                                        <a href="{{ route('attendance.corrections') }}" class="btn btn-outline-secondary btn-touch">Review</a>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item p-4">
                                <div class="empty-state py-2">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-shield-check text-success fs-2"></i></span>
                                    <span class="fw-semibold text-muted d-block mt-2">All clear. Operations running smoothly.</span>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var $pills = $('#dashboard-filter-pills .pill');
        var $feed = $('.js-feed-card');
        var $empty = $('#feed-empty');

        $pills.on('click', function (e) {
            e.preventDefault();
            var pill = $(this);
            var filter = String(pill.data('filter'));
            
            $pills.removeClass('active');
            pill.addClass('active');

            if (filter === 'all') {
                $feed.show();
            } else {
                var wanted = filter.split(',');
                $feed.each(function () {
                    var status = $(this).data('status');
                    $(this).toggle(wanted.indexOf(status) !== -1);
                });
            }
            $empty.toggleClass('d-none', $feed.filter(':visible').length > 0);
        });
    })(jQuery);
</script>
@endpush
