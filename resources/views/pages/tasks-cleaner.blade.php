@extends('layouts.app')

@section('title', 'My Tasks')

@push('styles')
<style>
    .my-tasks-header-bar {
        background-color: var(--cw-surface, #ffffff);
        border-bottom: 1px solid var(--cw-border, #e2e8f0);
    }
    .my-tasks-tab-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 2px solid #e2e8f0;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .my-tasks-tab-nav::-webkit-scrollbar {
        display: none;
    }
    .my-tasks-tab-item {
        font-family: var(--cw-font-mono, monospace);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 0.6rem 0.25rem;
        text-decoration: none;
        position: relative;
        transition: color 0.15s ease;
    }
    .my-tasks-tab-item:hover {
        color: #1e293b;
    }
    .my-tasks-tab-item.active {
        color: #0284c7;
    }
    .my-tasks-tab-item.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #0284c7;
        border-radius: 3px 3px 0 0;
    }
    .section-band-header {
        background-color: #e2e8f0;
        color: #475569;
        font-family: var(--cw-font-mono, monospace);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.4rem 1rem;
        margin: 1.25rem 0 0.75rem 0;
        border-radius: 4px;
        text-align: center;
    }
    .task-card-item {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .task-card-head {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .task-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.15rem;
        line-height: 1.3;
    }
    .task-card-address {
        font-size: 0.85rem;
        color: #64748b;
    }
    .task-card-body {
        padding: 0.75rem 1.25rem;
    }
    .task-card-type {
        font-size: 0.95rem;
        font-weight: 600;
        color: #334155;
        margin-top: 0.4rem;
        margin-bottom: 0.4rem;
    }
    .task-card-meta,
    .task-card-assignees {
        font-size: 0.85rem;
        color: #64748b;
    }
    .task-card-footer {
        border-top: 1px solid #f1f5f9;
        padding: 0.25rem 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        background-color: #fafafa;
    }
    .task-card-footer .icon-btn {
        min-height: 44px;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1.2rem;
        border-radius: 6px;
        text-decoration: none;
    }
    .task-card-footer .icon-btn:hover {
        color: #0284c7;
        background: #ffffff;
    }
    .task-card-footer .icon-btn.primary {
        color: #0284c7;
    }
    .task-tag-badge {
        background-color: #f1f5f9;
        color: #475569;
        font-family: var(--cw-font-mono, monospace);
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    @media (max-width: 575.98px) {
        .task-card-item { margin-bottom: 0.5rem; }
        .task-card-head { padding: 0.5rem 0.75rem; }
        .task-card-title { font-size: 0.875rem; }
        .task-card-address { font-size: 0.6875rem; }
        .task-card-body { padding: 0.5rem 0.75rem; }
        .task-card-meta { font-size: 0.6875rem; }
        .task-card-type { font-size: 0.8125rem; margin-top: 2px; margin-bottom: 2px; }
        .task-card-assignees { font-size: 0.6875rem; }
        .task-card-footer { padding: 2px 0.5rem; gap: 0; }
        .task-card-footer .icon-btn { min-height: 36px; min-width: 36px; font-size: 1rem; }
    }
</style>
@endpush

@section('content')
@php
    $search = $search ?? '';
    $sort = $sort ?? 'suggested';
    $tab = $tab ?? 'today';
    $counts = $counts ?? ['today' => 0, 'tomorrow' => 0, 'week' => 0, 'all' => 0, 'history' => 0];
    $overdueGroup = $overdueGroup ?? collect();
@endphp

<div class="container-fluid px-0">
    <!-- Top Action Bar matching Screenshot -->
    <div class="d-flex justify-content-between align-items-center mb-3 reveal">
        <div class="d-flex align-items-center gap-2">
            <h1 class="h3 fw-bold text-dark mb-0 ms-1">My tasks</h1>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Sort Filter Dropdown -->
            <form method="GET" action="{{ route('tasks.my') }}" class="d-inline" id="sort-form">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                <select name="sort" class="form-select form-select-sm bg-info-subtle border-0 text-info-emphasis fw-bold rounded-pill px-3 mono" onchange="document.getElementById('sort-form').submit();">
                    <option value="suggested" {{ $sort === 'suggested' ? 'selected' : '' }}>Sort by suggested ⇯</option>
                    <option value="scheduled" {{ $sort === 'scheduled' ? 'selected' : '' }}>Sort by time</option>
                    <option value="priority" {{ $sort === 'priority' ? 'selected' : '' }}>Sort by priority</option>
                </select>
            </form>

            <button type="button" class="btn btn-sm btn-light border rounded-circle" data-bs-toggle="collapse" data-bs-target="#search-collapse" aria-expanded="false">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>

    <!-- Search Bar Collapse -->
    <div class="collapse mb-3 {{ $search ? 'show' : '' }}" id="search-collapse">
        <form method="GET" action="{{ route('tasks.my') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                <input type="text" name="search" value="{{ $search }}" class="form-control border-0 px-3" placeholder="Search tasks by title, property, address...">
                <button type="submit" class="btn btn-primary px-3"><i class="bi bi-search me-1"></i>Search</button>
                @if($search)
                    <a href="{{ route('tasks.my', ['tab' => $tab]) }}" class="btn btn-outline-secondary px-2"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3 reveal" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Horizontal Date Navigation Tab Bar -->
    <div class="my-tasks-tab-nav mb-3 reveal">
        <a href="{{ route('calendar') }}" class="my-tasks-tab-item text-muted" title="View Calendar">
            <i class="bi bi-calendar3 fs-6"></i>
        </a>
        <a href="#" class="my-tasks-tab-item mine-tab {{ $tab === 'today' ? 'active' : '' }}" data-tab="today">
            TODAY @if($counts['today'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['today'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item mine-tab {{ $tab === 'tomorrow' ? 'active' : '' }}" data-tab="tomorrow">
            TOMORROW @if($counts['tomorrow'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['tomorrow'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item mine-tab {{ $tab === 'week' ? 'active' : '' }}" data-tab="week">
            WEEK @if($counts['week'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['week'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item mine-tab {{ $tab === 'all' ? 'active' : '' }}" data-tab="all" aria-label="Current tasks">
            ALL <span class="visually-hidden">Current tasks</span> @if($counts['all'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['all'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item mine-tab {{ $tab === 'history' || $tab === 'finished' ? 'active' : '' }}" data-tab="history" aria-label="Finished tasks">
            <i class="bi bi-clock-history me-1 text-primary"></i>TASK HISTORY <span class="visually-hidden">Finished tasks</span> @if($counts['history'] > 0)<span class="badge bg-success-subtle text-success rounded-pill ms-1">{{ $counts['history'] }}</span>@endif
        </a>
    </div>

    <!-- Active Tasks Stream (AJAX-swapped) -->
    <div id="mine-task-stream">
        @include('partials.task-cards', ['tasks' => $tasks, 'overdueGroup' => $overdueGroup, 'tab' => $tab])
    </div>

    <!-- Pagination links -->
    @if($tasks->hasPages())
        <div class="d-flex justify-content-center mb-4" id="mine-pagination">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        // AJAX tab switching — no page reload.
        $('.mine-tab').on('click', function (e) {
            e.preventDefault();
            var $tab = $(this);
            if ($tab.hasClass('active')) return;
            $('.mine-tab').removeClass('active');
            $tab.addClass('active');
            $('#mine-task-stream').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Loading…</div>');
            axios.get('{{ route('tasks.my') }}', { params: { tab: $tab.data('tab'), sort: '{{ $sort }}' } })
                .then(function (res) {
                    $('#mine-task-stream').html(res.data);
                })
                .catch(function () {
                    $('#mine-task-stream').html('<div class="text-center py-5 text-danger">Failed to load.</div>');
                });
        });
    })(jQuery);
</script>
@endpush
