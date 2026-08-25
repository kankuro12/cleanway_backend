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

    <!-- Tab nav: date shortcuts + filter tab -->
    <div class="my-tasks-tab-nav mb-3 reveal">
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'today' ? 'active' : '' }}" data-tab="today">
            TODAY <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 {{ $counts['today'] > 0 ? '' : 'd-none' }}" id="tab-count-today">{{ $counts['today'] }}</span>
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'tomorrow' ? 'active' : '' }}" data-tab="tomorrow">
            TOMORROW <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 {{ $counts['tomorrow'] > 0 ? '' : 'd-none' }}" id="tab-count-tomorrow">{{ $counts['tomorrow'] }}</span>
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'week' ? 'active' : '' }}" data-tab="week">
            WEEK <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 {{ $counts['week'] > 0 ? '' : 'd-none' }}" id="tab-count-week">{{ $counts['week'] }}</span>
        </a>
        <a href="#" class="my-tasks-tab-item reg-tab {{ $tab === 'filters' ? 'active' : '' }}" data-tab="filters">
            <i class="bi bi-funnel me-1" aria-hidden="true"></i>FILTER
        </a>
    </div>

    <!-- Filter pane — shown only when FILTER tab is active -->
    <div id="filter-tab-pane" class="{{ $tab === 'filters' ? '' : 'd-none' }}">
        @include('partials.compact-filter-bar', ['searchNames' => []])

        <form method="GET" id="filter-form" class="filter-form mb-3 reveal" style="--d: 80ms">
            <input type="hidden" name="tab" value="filters">
            <input type="hidden" name="apply" value="1">
            <div class="filter-sheet-head">
                <span class="mono text-muted">Filter options</span>
                <button type="button" class="btn btn-icon-touch" data-filter-close aria-label="Close filters">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="row g-2 filter-sheet-body">
                <div class="col-12">
                    <label for="property_id" class="form-label fw-semibold">Property</label>
                    <select name="property_id" id="property_id" class="form-select form-select-sm select2-searchable">
                        <option value="">All Properties</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}" @selected(request('property_id') == $property->id)>{{ $property->dropdown_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" name="from" id="from" value="{{ request('from', today()->toDateString()) }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" name="to" id="to" value="{{ request('to', today()->toDateString()) }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="not_started" @selected(request('status') === 'not_started')>Not Started</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="task_type_id" class="form-label">Type</label>
                    <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach ($taskTypes as $type)
                            <option value="{{ $type->id }}" @selected(request('task_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="assignee_id" class="form-label">Assignee</label>
                    <select name="assignee_id" id="assignee_id" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected(request('assignee_id') == $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-none d-md-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('tasks', ['tab' => 'filters']) }}" class="btn btn-sm btn-outline-secondary" aria-label="Clear filters"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter</button>
                </div>
            </div>
            <div class="filter-sheet-foot">
                <button type="submit" class="btn btn-touch"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Apply filters</button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div id="reg-task-list">
            @include('partials.task-list', ['tasks' => $tasks])
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $tasks->links() }}</div>

    <!-- Supervisor Quick Schedule Edit Modal -->
    <div class="modal fade" id="quickScheduleModal" tabindex="-1" aria-labelledby="quickScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-quick-schedule">
                    <div class="modal-header py-2">
                        <h5 class="modal-title fs-6 fw-bold" id="quickScheduleModalLabel"><i class="bi bi-calendar-event me-1 text-primary"></i>Edit Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <input type="hidden" id="qs-task-id">
                        <div class="mb-2">
                            <span class="text-muted extra-small d-block">Task:</span>
                            <span class="fw-bold fs-6" id="qs-task-title"></span>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label for="qs-start-at" class="form-label small fw-semibold">Scheduled Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="qs-start-at" name="scheduled_start_at" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label for="qs-end-at" class="form-label small fw-semibold">Scheduled End Date & Time</label>
                                <input type="datetime-local" id="qs-end-at" name="scheduled_end_at" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="alert alert-danger py-1.5 extra-small mt-2 d-none" id="qs-error"></div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="qs-submit-btn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="qs-spinner"></span>Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Supervisor Quick Assign Personnel Modal -->
    <div class="modal fade" id="quickAssignModal" tabindex="-1" aria-labelledby="quickAssignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="form-quick-assign">
                    <div class="modal-header py-2">
                        <h5 class="modal-title fs-6 fw-bold" id="quickAssignModalLabel"><i class="bi bi-people-fill me-1 text-primary"></i>Assign Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <input type="hidden" id="qa-task-id">
                        <div class="mb-2">
                            <span class="text-muted extra-small d-block">Task:</span>
                            <span class="fw-bold fs-6" id="qa-task-title"></span>
                        </div>
                        <div class="mb-2">
                            <input type="search" class="form-control form-control-sm" id="qa-search-input" placeholder="Search cleaners or supervisors…">
                        </div>
                        <div class="list-group list-group-flush border rounded" id="qa-user-list" style="max-height: 280px; overflow-y: auto;">
                            @foreach ($assignees as $assignee)
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 qa-user-item" data-name="{{ strtolower($assignee->name) }}">
                                    <input class="form-check-input flex-shrink-0 m-0 qa-user-checkbox" type="checkbox" name="assignee_ids[]" value="{{ $assignee->id }}">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-dark small text-truncate">{{ $assignee->name }}</div>
                                        <div class="extra-small text-muted mono">{{ $assignee->role == 1 ? 'Supervisor' : 'Cleaner' }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="alert alert-danger py-1.5 extra-small mt-2 d-none" id="qa-error"></div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="qa-submit-btn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="qa-spinner"></span>Save Assignees
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function ($) {
        // Initialize Select2 on property select
        function initSelect2() {
            if ($.fn.select2) {
                $('#property_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'All Properties',
                    allowClear: true,
                    width: '100%'
                });
            }
        }
        initSelect2();

        // AJAX tab switching — no page reload.
        $('.reg-tab').on('click', function (e) {
            e.preventDefault();
            var $tab = $(this);
            if ($tab.hasClass('active')) return;

            var tabName = $tab.data('tab');
            $('.reg-tab').removeClass('active');
            $tab.addClass('active');

            if (tabName === 'filters') {
                $('#filter-tab-pane').removeClass('d-none');
                initSelect2();
                if (window.matchMedia('(max-width: 767.98px)').matches) {
                    $('#filter-form').addClass('open');
                    $('#filter-toggle').attr('aria-expanded', 'true');
                }
                $('#reg-task-list').html('<div class="empty-state py-5"><span class="empty-state-icon" aria-hidden="true"><i class="bi bi-funnel"></i></span><div class="fw-semibold text-dark mb-1">Set filters and click "Filter" to search</div><small class="text-muted">Choose your date range, property, status, or assignee above to load tasks.</small></div>');
                return;
            } else {
                $('#filter-tab-pane').addClass('d-none');
                $('#filter-form').removeClass('open');
                $('#filter-toggle').attr('aria-expanded', 'false');
            }

            $('#reg-task-list').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Loading…</div>');

            axios.get('{{ route('tasks') }}', { params: { tab: tabName } })
                .then(function (res) {
                    $('#reg-task-list').html(res.data);
                })
                .catch(function () {
                    $('#reg-task-list').html('<div class="text-center py-5 text-danger">Failed to load.</div>');
                });
        });

        function buildTaskFilterPills() {
            var $pillsWrap = $('#filter-pills');
            $pillsWrap.empty();
            var activeCount = 0;

            $('#filter-form').find('select, input[type!="hidden"]').each(function () {
                var el = this;
                var name = el.name;
                var val = $(el).val();

                if (!name || /^(apply|tab|page)$/i.test(name) || val === '' || val === null) return;

                var label = $('label[for="' + el.id + '"]').first().text().trim() || name;
                var valText = '';

                if (el.tagName === 'SELECT') {
                    if (el.selectedOptions && el.selectedOptions[0]) {
                        valText = el.selectedOptions[0].textContent.trim();
                        if (valText.toLowerCase().startsWith('all ')) {
                            return;
                        }
                    }
                } else {
                    valText = val;
                }

                if (!valText) return;
                activeCount++;

                var $pill = $('<a href="#" class="pill"></a>');
                $pill.append($('<span></span>').text(label + ': ' + valText));
                $pill.append(' <i class="bi bi-x-lg ms-1 text-muted" aria-hidden="true" title="Remove filter"></i>');

                $pill.on('click', function (e) {
                    e.preventDefault();
                    if ($(el).is('select')) {
                        $(el).val('').trigger('change');
                    } else {
                        $(el).val('');
                    }
                    $('#filter-form').trigger('submit');
                });

                $pillsWrap.append($pill);
            });

            if (activeCount > 0) {
                var $clearAll = $('<a href="#" class="pill text-danger bg-danger-subtle border-danger-subtle"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear all</a>');
                $clearAll.on('click', function (e) {
                    e.preventDefault();
                    $('#filter-form').find('select').val('').trigger('change');
                    $('#filter-form').find('input[type="date"], input[type="text"], input[type="search"]').val('');
                    $('#filter-pills').empty().addClass('d-none');
                    $('#filter-form').trigger('submit');
                });
                $pillsWrap.append($clearAll);

                $pillsWrap.removeClass('d-none');
                $('#filter-toggle').find('.filter-count').remove();
                $('#filter-toggle').append('<span class="badge text-bg-primary ms-1 filter-count">' + activeCount + '</span>');
            } else {
                $pillsWrap.addClass('d-none');
                $('#filter-toggle').find('.filter-count').remove();
            }
        }

        // AJAX submit for filter form
        $('#filter-form').on('submit', function (e) {
            e.preventDefault();

            // 1. Close mobile bottom sheet
            $('#filter-form').removeClass('open');
            $('#filter-toggle').attr('aria-expanded', 'false');

            // 2. Render smaller pills for active filters
            buildTaskFilterPills();

            $('#reg-task-list').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>Searching tasks…</div>');

            var params = { tab: 'filters', apply: '1' };
            var formData = $(this).serializeArray();
            $.each(formData, function (_, field) {
                if (field.value) {
                    params[field.name] = field.value;
                }
            });

            axios.get('{{ route('tasks') }}', { params: params })
                .then(function (res) {
                    $('#reg-task-list').html(res.data);
                })
                .catch(function () {
                    $('#reg-task-list').html('<div class="text-center py-5 text-danger">Failed to load filtered tasks.</div>');
                });
        });

        // Auto popup filter bottom sheet on mobile when tab=filters
        @if($tab === 'filters')
            if (window.matchMedia('(max-width: 767.98px)').matches) {
                $('#filter-form').addClass('open');
                $('#filter-toggle').attr('aria-expanded', 'true');
            }
            @if(request('apply'))
                buildTaskFilterPills();
            @endif
        @endif

        // Quick Schedule Modal Handler
        $(document).on('click', '.js-quick-schedule-trigger', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var taskId = $btn.data('task-id');
            var taskTitle = $btn.data('task-title');
            var startAt = $btn.data('start') || '';
            var endAt = $btn.data('end') || '';

            $('#qs-task-id').val(taskId);
            $('#qs-task-title').text(taskTitle);
            $('#qs-start-at').val(startAt);
            $('#qs-end-at').val(endAt);
            $('#qs-error').addClass('d-none').text('');

            $('#quickScheduleModal').data('custom-hash', '#schedule-' + taskId);
            var modal = new bootstrap.Modal(document.getElementById('quickScheduleModal'));
            modal.show();
        });

        function updateTabBadgeCounts(counts) {
            if (!counts) return;
            if (counts.today > 0) {
                $('#tab-count-today').text(counts.today).removeClass('d-none');
            } else {
                $('#tab-count-today').addClass('d-none');
            }
            if (counts.tomorrow > 0) {
                $('#tab-count-tomorrow').text(counts.tomorrow).removeClass('d-none');
            } else {
                $('#tab-count-tomorrow').addClass('d-none');
            }
            if (counts.week > 0) {
                $('#tab-count-week').text(counts.week).removeClass('d-none');
            } else {
                $('#tab-count-week').addClass('d-none');
            }
        }

        function reloadActiveTab() {
            var $activeTab = $('.reg-tab.active');
            var tabName = $activeTab.data('tab');
            if (tabName === 'filters') {
                $('#filter-form').trigger('submit');
            } else {
                axios.get('{{ route('tasks') }}', { params: { tab: tabName } })
                    .then(function (res) {
                        $('#reg-task-list').html(res.data);
                    });
            }
        }

        $('#form-quick-schedule').on('submit', function (e) {
            e.preventDefault();
            var taskId = $('#qs-task-id').val();
            var startAt = $('#qs-start-at').val();
            var endAt = $('#qs-end-at').val();

            $('#qs-spinner').removeClass('d-none');
            $('#qs-submit-btn').prop('disabled', true);
            $('#qs-error').addClass('d-none');

            axios.post('/admin/tasks/' + taskId + '/quick-schedule', {
                scheduled_start_at: startAt,
                scheduled_end_at: endAt
            }).then(function (res) {
                $('#qs-spinner').addClass('d-none');
                $('#qs-submit-btn').prop('disabled', false);
                bootstrap.Modal.getInstance(document.getElementById('quickScheduleModal'))?.hide();

                // Update tab badge counts
                updateTabBadgeCounts(res.data.counts);

                // Check whether task still belongs to the active tab / date range
                var currentTab = $('.reg-tab.active').data('tab');
                var shouldRemove = false;

                if (currentTab === 'today' && !res.data.is_today) {
                    shouldRemove = true;
                } else if (currentTab === 'tomorrow' && !res.data.is_tomorrow) {
                    shouldRemove = true;
                } else if (currentTab === 'week' && !res.data.is_this_week) {
                    shouldRemove = true;
                } else if (currentTab === 'filters') {
                    var from = $('#from').val();
                    var to = $('#to').val();
                    var taskDate = res.data.scheduled_date;
                    if ((from && taskDate < from) || (to && taskDate > to)) {
                        shouldRemove = true;
                    }
                }

                if (shouldRemove) {
                    // Task moved to another date/range: remove from view smoothly
                    var $row = $('#task-row-' + taskId);
                    var $card = $('#task-card-' + taskId);

                    $row.fadeOut(300, function () {
                        $row.remove();
                        if ($('#reg-task-list tbody tr').length === 0) {
                            reloadActiveTab();
                        }
                    });
                    $card.fadeOut(300, function () {
                        $card.remove();
                    });
                } else {
                    // Update text and data attributes in the row & card
                    var formatted = res.data.formatted_schedule;
                    $('#task-schedule-text-' + taskId).html(formatted);
                    $('.js-quick-schedule-trigger[data-task-id="' + taskId + '"]')
                        .data('start', res.data.scheduled_start_at)
                        .data('end', res.data.scheduled_end_at);
                }
            }).catch(function (err) {
                $('#qs-spinner').addClass('d-none');
                $('#qs-submit-btn').prop('disabled', false);
                var msg = err.response?.data?.message || 'Failed to update schedule.';
                $('#qs-error').removeClass('d-none').text(msg);
            });
        });

        // Quick Assign Modal Handler
        $(document).on('click', '.js-quick-assign-trigger', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var taskId = $btn.data('task-id');
            var taskTitle = $btn.data('task-title');
            var assignedIds = $btn.data('assigned-ids') || [];
            if (typeof assignedIds === 'string') {
                try { assignedIds = JSON.parse(assignedIds); } catch (e) { assignedIds = []; }
            }

            $('#qa-task-id').val(taskId);
            $('#qa-task-title').text(taskTitle);
            $('#qa-search-input').val('');
            $('.qa-user-item').removeClass('d-none');
            $('#qa-error').addClass('d-none').text('');

            $('.qa-user-checkbox').each(function () {
                var uid = parseInt($(this).val(), 10);
                $(this).prop('checked', assignedIds.includes(uid));
            });

            $('#quickAssignModal').data('custom-hash', '#assign-' + taskId);
            var modal = new bootstrap.Modal(document.getElementById('quickAssignModal'));
            modal.show();
        });

        // Search user filter in assign modal
        $('#qa-search-input').on('input', function () {
            var q = $(this).val().toLowerCase().trim();
            $('.qa-user-item').each(function () {
                var name = $(this).data('name') || '';
                $(this).toggleClass('d-none', q !== '' && name.indexOf(q) === -1);
            });
        });

        $('#form-quick-assign').on('submit', function (e) {
            e.preventDefault();
            var taskId = $('#qa-task-id').val();
            var selectedIds = [];
            $('.qa-user-checkbox:checked').each(function () {
                selectedIds.push(parseInt($(this).val(), 10));
            });

            $('#qa-spinner').removeClass('d-none');
            $('#qa-submit-btn').prop('disabled', true);
            $('#qa-error').addClass('d-none');

            axios.post('/admin/tasks/' + taskId + '/quick-assign', {
                assignee_ids: selectedIds
            }).then(function (res) {
                $('#qa-spinner').addClass('d-none');
                $('#qa-submit-btn').prop('disabled', false);
                bootstrap.Modal.getInstance(document.getElementById('quickAssignModal'))?.hide();

                // Update badges on row
                var assignees = res.data.assignees || [];
                var badgesHtml = '';
                if (assignees.length > 0) {
                    $.each(assignees, function (_, a) {
                        badgesHtml += '<span class="status-badge status-muted">' + (a.name || ('#' + a.assignee_id)) + '</span> ';
                    });
                } else {
                    badgesHtml = '<span class="badge bg-secondary-subtle text-secondary border border-dashed"><i class="bi bi-person-plus me-1"></i>Assign</span>';
                }

                $('#task-assignees-badges-' + taskId).html(badgesHtml);
                $('.js-quick-assign-trigger[data-task-id="' + taskId + '"]').data('assigned-ids', selectedIds);
            }).catch(function (err) {
                $('#qa-spinner').addClass('d-none');
                $('#qa-submit-btn').prop('disabled', false);
                var msg = err.response?.data?.message || 'Failed to update assignees.';
                $('#qa-error').removeClass('d-none').text(msg);
            });
        });
    })(jQuery);
</script>
@endpush
