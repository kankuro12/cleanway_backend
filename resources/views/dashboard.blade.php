@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Overview</span>
            <h2 class="h3 mt-1 mb-0">Today's operations</h2>
        </div>
        <span class="role-chip">Shift {{ now()->format('D, d M Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($widgets['stats'] as $i => $stat)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="stat-card reveal" style="--d: {{ $i * 40 }}ms">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-card-value">{{ $stat['value'] }}</div>
                            <div class="stat-card-label">{{ $stat['label'] }}</div>
                        </div>
                        <i class="bi bi-{{ $stat['icon'] }}" style="font-size: 1.5rem; color: var(--cw-faint)" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(isset($widgets['next']) && $widgets['next'])
        <div class="alert alert-warning py-2 reveal" role="alert">
            <i class="bi bi-stopwatch me-1" aria-hidden="true"></i>
            <strong>Next task:</strong> {{ $widgets['next']->title }}
            @if($widgets['next']->property_name_snapshot) at {{ $widgets['next']->property_name_snapshot }}@endif
            — {{ $widgets['next']->scheduled_start_at?->format('H:i') }}
            <a href="{{ route('tasks.edit', $widgets['next']) }}" class="alert-link ms-2">Open</a>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card reveal" style="--d: 220ms">
                <div class="card-header mono">Tasks today</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($widgets['today'] as $task)
                                <tr>
                                    <td class="mono small">{{ $task->reference_number }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $task->title }}</span>
                                        @if($task->assignments->isNotEmpty())
                                            <br><small class="text-muted">
                                                @foreach ($task->assignments as $a){{ $a->assignee?->name ?? '#' . $a->assignee_id }}@if(!$loop->last), @endif @endforeach
                                            </small>
                                        @endif
                                    </td>
                                    <td><span class="status-badge status-muted">{{ str_replace('_', ' ', $task->status) }}</span></td>
                                    <td class="small">{{ $task->scheduled_start_at?->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
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

        <div class="col-lg-5">
            @foreach ($widgets['attention'] as $section => $items)
                <div class="card reveal mb-3" style="--d: 280ms">
                    <div class="card-header mono">{{ $section }}</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($items as $item)
                            <li class="list-group-item small">
                                @if(isset($item->event))
                                    {{ $item->event?->user?->name }} — {{ $item->reason ?? 'GPS exception' }}
                                @elseif(isset($item->reporter))
                                    {{ $item->description }}
                                    <span class="status-badge status-{{ $item->severity === 'critical' ? 'danger' : 'warning' }} ms-1">{{ $item->severity }}</span>
                                @else
                                    {{ $item->reason ?? $item->title ?? '—' }}
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item">
                                <div class="empty-state py-3">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                                    Nothing here.
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection
