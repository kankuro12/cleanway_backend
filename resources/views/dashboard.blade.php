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
        @php
            $stats = [
                ['value' => '12', 'label' => 'Active tasks', 'icon' => 'clipboard-check', 'd' => '0ms'],
                ['value' => '04', 'label' => 'Pending approval', 'icon' => 'hourglass-split', 'd' => '60ms'],
                ['value' => '18', 'label' => 'Personnel on site', 'icon' => 'people', 'd' => '120ms'],
                ['value' => '02', 'label' => 'GPS exceptions', 'icon' => 'geo-alt', 'd' => '180ms'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="col-6 col-xl-3">
                <div class="stat-card reveal" style="--d: {{ $stat['d'] }}">
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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card reveal" style="--d: 220ms">
                <div class="card-header">Assignments queue</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Task</th>
                                <th>Assignee</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ([] as $task)
                                <tr>...</tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-inbox"></i></span>
                                            No tasks scheduled today.
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
            <div class="card reveal" style="--d: 280ms">
                <div class="card-header">Attention needed</div>
                <ul class="list-group list-group-flush">
                    @forelse ([] as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $item }}</span>
                        </li>
                    @empty
                        <li class="list-group-item">
                            <div class="empty-state py-4">
                                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                                All clear.
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
