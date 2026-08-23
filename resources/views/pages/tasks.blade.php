@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    @php
        $tab = $tab ?? 'today';
        $counts = $counts ?? ['today' => 0, 'tomorrow' => 0, 'week' => 0, 'all' => 0];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3 reveal">
        <div>
            <span class="eyebrow">Tasks · Register</span>
            <h1 class="h3 mt-1 mb-0">Task register</h1>
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

    <!-- Tab nav: date shortcuts -->
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
    </div>

    @include('partials.compact-filter-bar', ['searchNames' => []])

    <form method="GET" id="filter-form" class="filter-form mb-3 reveal" style="--d: 80ms">
        <input type="hidden" name="tab" value="all">
        <div class="filter-sheet-head">
            <span class="mono text-muted">Filter options</span>
            <button type="button" class="btn btn-icon-touch" data-filter-close aria-label="Close filters">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="row g-2 filter-sheet-body">
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
            <div class="col-12 col-md-2 d-none d-md-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter</button>
                <a href="{{ route('tasks') }}" class="btn btn-sm btn-outline-secondary" aria-label="Clear filters"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
            </div>
        </div>
        <div class="filter-sheet-foot">
            <button type="submit" class="btn btn-touch"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Apply filters</button>
        </div>
    </form>

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
            if ($tab.hasClass('active')) return;

            $('.reg-tab').removeClass('active');
            $tab.addClass('active');

            $('#reg-task-list').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Loading…</div>');
            axios.get('{{ route('tasks') }}', { params: { tab: $tab.data('tab') } })
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
