@extends('layouts.app')

@section('title', 'Tasks')

@push('styles')
<style>
    .my-tasks-tab-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 2px solid var(--cw-border, #e2e8f0);
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .my-tasks-tab-nav::-webkit-scrollbar { display: none; }
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
        white-space: nowrap;
    }
    .my-tasks-tab-item:hover { color: #1e293b; }
    .my-tasks-tab-item.active { color: #0284c7; }
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

    /* Compact Filter Controls for Mobile */
    @media (max-width: 575.98px) {
        #filter-sheet .card-body {
            padding: 0.65rem 0.75rem !important;
        }
        #filter-sheet .form-label {
            font-size: 0.68rem !important;
            font-family: var(--cw-font-mono, monospace);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 2px !important;
            font-weight: 600;
        }
        #filter-sheet .form-control-sm,
        #filter-sheet .form-select-sm {
            font-size: 0.78rem !important;
            height: 30px !important;
            min-height: 30px !important;
            padding: 2px 8px !important;
            border-radius: 6px !important;
        }
        #filter-sheet .btn {
            font-size: 0.78rem !important;
            min-height: 32px !important;
            height: 32px !important;
            padding: 0 10px !important;
            border-radius: 6px !important;
        }
        #filter-sheet .row.g-2 {
            --bs-gutter-x: 0.4rem;
            --bs-gutter-y: 0.4rem;
        }
    }
</style>
@endpush

@section('content')
    @php
        $tab = $tab ?? 'today';
        $counts = $counts ?? ['today' => 0, 'tomorrow' => 0, 'week' => 0, 'all' => 0];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3 reveal">
        <div>
            <span class="eyebrow">Tasks · Register</span>
            <h1 class="h4 mt-1 mb-0">Task register</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.worksheet') }}" class="btn btn-outline-success btn-sm" title="View tasks in Excel spreadsheet format">
                <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Work Sheet
            </a>
            <a href="{{ route('calendar') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Calendar
            </a>
            <a href="{{ route('recurrences') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex">
                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Recurrences
            </a>
            @if(auth()->user()->hasPermission('4.2'))
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>New task
                </a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <!-- Tab nav: date shortcuts + Filters as last tab -->
    <div class="my-tasks-tab-nav mb-3 reveal">
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'today' ? 'active' : '' }}" data-tab="today">
            TODAY @if($counts['today'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['today'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'tomorrow' ? 'active' : '' }}" data-tab="tomorrow">
            TOMORROW @if($counts['tomorrow'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['tomorrow'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'week' ? 'active' : '' }}" data-tab="week">
            WEEK @if($counts['week'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['week'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'all' ? 'active' : '' }}" data-tab="all">
            ALL @if($counts['all'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['all'] }}</span>@endif
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'filters' ? 'active' : '' }}" data-tab="filters">
            <i class="bi bi-sliders me-1"></i>FILTERS
        </a>
    </div>

    <!-- Filters sheet (visible ONLY on Filters tab) -->
    <div id="filter-sheet" style="{{ $tab === 'filters' ? '' : 'display:none' }}">
        <form method="GET" id="filter-form" class="mb-3 reveal" style="--d: 80ms" role="search">
            <input type="hidden" name="tab" value="filters">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-2">
                            <label for="from" class="form-label">From</label>
                            <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="to" class="form-label">To</label>
                            <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach (['draft', 'scheduled', 'unassigned', 'assigned', 'accepted', 'declined', 'in_progress', 'paused', 'delayed', 'unable_to_access', 'completed', 'submitted_for_approval', 'correction_requested', 'rejected', 'reopened', 'approved', 'cancelled'] as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                    <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="task_type_id" class="form-label">Type</label>
                            <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($taskTypes as $type)
                                    <option value="{{ $type->id }}" @selected(request('task_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="property_id" class="form-label">Property</label>
                            <select name="property_id" id="property_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" @selected(request('property_id') == $property->id)>{{ $property->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="assignee_id" class="form-label">Assignee</label>
                            <select name="assignee_id" id="assignee_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" @selected(request('assignee_id') == $assignee->id)>{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-touch flex-fill"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Apply filters</button>
                            <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-touch"><i class="bi bi-x-lg me-1"></i>Clear</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div id="reg-task-list">
            @include('partials.task-list', ['tasks' => $tasks])
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $tasks->links() }}</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        // AJAX tab switching — no page reload.
        $('.reg-tab').on('click', function (e) {
            e.preventDefault();
            var $tab = $(this);
            var name = $tab.data('tab');

            if ($tab.hasClass('active') && name !== 'filters') return;

            $('.reg-tab').removeClass('active');
            $tab.addClass('active');

            if (name === 'filters') {
                $('#filter-sheet').slideDown(150);
                return;
            }

            // For all other tabs, hide the filter sheet completely
            $('#filter-sheet').slideUp(150);
            $('#reg-task-list').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Loading…</div>');
            axios.get('{{ route('tasks') }}', { params: { tab: name } })
                .then(function (res) {
                    $('#reg-task-list').html(res.data);
                })
                .catch(function () {
                    $('#reg-task-list').html('<div class="text-center py-5 text-danger">Failed to load.</div>');
                });
        });
    })(jQuery);
</script>
@endpush
