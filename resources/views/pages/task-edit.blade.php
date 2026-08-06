@extends('layouts.app')

@section('title', $task->title)

@push('styles')
    <style>
        .toast-banner {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1060;
            min-width: 280px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            display: none;
        }
        .completed-subtask {
            text-decoration: line-through;
            color: var(--cw-muted);
        }
    </style>
@endpush

@section('content')
    <!-- Live Toast Status Feedback Banner -->
    <div id="ajax-toast-banner" class="toast-banner alert alert-success d-flex align-items-center gap-2 p-3 rounded" role="alert">
        <i id="ajax-toast-icon" class="bi bi-check-circle-fill fs-5"></i>
        <div id="ajax-toast-text" class="fw-semibold small">Saved successfully</div>
    </div>

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · {{ $task->reference_number }}</span>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h1 class="h3 mb-0 font-weight-bold" id="task-header-title">{{ $task->title }}</h1>
                <span class="status-badge status-{{ $task->status === 'in_progress' ? 'warning' : ($task->status === 'completed' ? 'active' : 'muted') }}" id="task-status-badge">
                    {{ str_replace('_', ' ', $task->status) }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-touch">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Task Register
            </a>
            @if(auth()->user()->hasPermission('4.6'))
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-touch">
                        <i class="bi bi-trash me-1" aria-hidden="true"></i>Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Details, Subtasks, History (7 Cols) -->
        <div class="col-lg-7">
            <!-- Form 1: Details & Schedule (Axios Auto-Save) -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 80ms">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pencil-square me-1 text-accent"></i>Task Details & Schedule</span>
                    <span class="badge bg-secondary extra-small mono" id="details-save-status">Auto-Save</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task) }}" id="form-task-details">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" value="{{ $task->title }}" class="form-control" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select">
                                    @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                        <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="estimated_duration_minutes" class="form-label">Duration (min)</label>
                                <input type="number" min="1" id="estimated_duration_minutes" name="estimated_duration_minutes" value="{{ $task->estimated_duration_minutes }}" class="form-control">
                            </div>
                            <div class="col-6 col-md-6">
                                <label for="scheduled_start_at" class="form-label">Starts at</label>
                                <input type="datetime-local" id="scheduled_start_at" name="scheduled_start_at"
                                       value="{{ $task->scheduled_start_at?->format('Y-m-d\TH:i') }}" class="form-control">
                            </div>
                            <div class="col-6 col-md-6">
                                <label for="scheduled_end_at" class="form-label">Ends at</label>
                                <input type="datetime-local" id="scheduled_end_at" name="scheduled_end_at"
                                       value="{{ $task->scheduled_end_at?->format('Y-m-d\TH:i') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" rows="3" class="form-control">{{ $task->description }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-sm btn-primary fw-bold" id="btn-save-details">
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($task->checklistSnapshot->isNotEmpty())
                <div class="card shadow-sm mb-3 reveal" style="--d: 120ms">
                    <div class="card-header mono"><i class="bi bi-clipboard-data me-1 text-accent"></i>Checklist Snapshot (Immutable)</div>
                    <div class="card-body">
                        @foreach ($task->checklistSnapshot->groupBy('section_name') as $section => $items)
                            <div class="fw-semibold small text-uppercase text-muted mb-1">{{ $section }}</div>
                            <ul class="list-unstyled ms-2 mb-3">
                                @foreach ($items as $item)
                                    <li class="small">
                                        <i class="bi bi-{{ $item->item_type === 'photo' ? 'camera' : ($item->item_type === 'text' ? 'font' : ($item->item_type === 'numeric' ? '123' : 'check-circle')) }} me-1" aria-hidden="true"></i>
                                        {{ $item->item_label }}
                                        @if($item->required)<span class="text-danger">*</span>@endif
                                        <span class="status-badge status-muted ms-1">{{ str_replace('_', ' ', $item->item_type) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Form 2: Subtasks (Axios Async Toggle & Add) -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 140ms">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-check2-square me-1 text-accent"></i>Subtasks</span>
                    <span class="badge bg-secondary rounded-pill" id="subtasks-count">{{ $task->subtasks->count() }}</span>
                </div>
                <div class="card-body">
                    <div id="subtasks-list-container">
                        @forelse ($task->subtasks as $subtask)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom subtask-item-row" data-subtask-id="{{ $subtask->id }}">
                                <span class="small subtask-title-text {{ $subtask->completed_at ? 'completed-subtask' : '' }}">
                                    <i class="bi bi-{{ $subtask->completed_at ? 'check2-circle text-success' : 'circle text-muted' }} me-1 subtask-icon"></i>
                                    {{ $subtask->title }}
                                </span>
                                @if(auth()->user()->hasPermission('4.4'))
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-toggle-subtask {{ $subtask->completed_at ? 'text-muted' : 'text-success' }}"
                                            data-url="{{ route('tasks.subtasks.toggle', [$task, $subtask]) }}">
                                        {{ $subtask->completed_at ? 'Reopen' : 'Done' }}
                                    </button>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-2" id="subtasks-empty">No sub tasks added yet.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('tasks.subtasks.store', $task) }}" id="form-add-subtask" class="mt-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="text" name="title" id="new-subtask-title" class="form-control" placeholder="Add a sub task…" required>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Subtask
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status History Table -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 160ms">
                <div class="card-header mono"><i class="bi bi-clock-history me-1 text-accent"></i>Status History</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>From</th><th>To</th><th>By</th><th>When</th><th>Remarks</th></tr></thead>
                        <tbody id="history-table-body">
                        @forelse ($task->history as $entry)
                            <tr>
                                <td class="small">{{ str_replace('_', ' ', $entry->previous_status ?? '—') }}</td>
                                <td class="small"><span class="status-badge status-muted">{{ str_replace('_', ' ', $entry->new_status) }}</span></td>
                                <td class="small">{{ $entry->user?->name ?? 'system' }}</td>
                                <td class="small text-muted">{{ $entry->created_at?->format('j M H:i') }}</td>
                                <td class="small text-muted">{{ $entry->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr id="history-empty-row"><td colspan="5" class="text-muted small py-3 text-center">No transitions recorded yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Status Transition, Assignments, Snapshot (5 Cols) -->
        <div class="col-lg-5">
            <!-- Form 3: Status Transition (Axios Async Move) -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 100ms">
                <div class="card-header mono d-flex justify-content-between align-items-center" id="status-card-header">
                    <span><i class="bi bi-arrow-right-circle me-1 text-accent"></i>Status</span>
                    <span class="status-badge status-warning" id="status-card-header-badge">{{ str_replace('_', ' ', $task->status) }}</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.transition', $task) }}" id="form-move-status" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label for="status" class="form-label small font-weight-bold">Select New Status</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                @foreach ($task->transitionableStatuses() as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label small font-weight-bold">Transition Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100 fw-bold">
                                <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i>Move Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form 4: Assignments (Axios Async Add & Remove) -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 140ms">
                <div class="card-header mono"><i class="bi bi-people me-1 text-accent"></i>Assignments</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.assign', $task) }}" id="form-add-assignee" class="row g-2">
                        @csrf
                        <div class="col-6">
                            <label for="assignee_type" class="form-label extra-small">Type</label>
                            <select name="assignee_type" id="assignee_type" class="form-select form-select-sm" onchange="document.getElementById('assignee_user_col').classList.toggle('d-none', this.value !== 'user'); document.getElementById('assignee_team_col').classList.toggle('d-none', this.value !== 'team'); document.getElementById('assignee_id_user').disabled = (this.value !== 'user'); document.getElementById('assignee_id_team').disabled = (this.value !== 'team');">
                                <option value="user">Person</option>
                                <option value="team">Team</option>
                            </select>
                        </div>
                        <div class="col-6" id="assignee_user_col">
                            <label for="assignee_id_user" class="form-label extra-small">Assignee Person</label>
                            <select name="assignee_id" id="assignee_id_user" class="form-select form-select-sm" required>
                                <option value="">Select Person</option>
                                @foreach ($people ?? $cleaners as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 d-none" id="assignee_team_col">
                            <label for="assignee_id_team" class="form-label extra-small">Assignee Team</label>
                            <select name="assignee_id" id="assignee_id_team" class="form-select form-select-sm" disabled required>
                                <option value="">Select Team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="form-check form-switch d-inline-block me-2">
                                <input class="form-check-input" type="checkbox" name="override_warnings" value="1" id="override_warnings">
                                <label class="form-check-label extra-small" for="override_warnings">Override warnings</label>
                            </div>
                            <input type="text" name="override_reason" class="form-control form-control-sm mt-1" placeholder="Override reason">
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100 fw-bold">
                                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add Assignee
                            </button>
                        </div>
                    </form>
                    <hr>
                    <div id="assignments-list-container">
                        @forelse ($task->assignments as $assignment)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom assignment-row-item" data-assignment-id="{{ $assignment->id }}">
                                <div>
                                    <span class="fw-semibold small">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                                    <span class="status-badge status-muted ms-2">{{ $assignment->assignee_type }}</span>
                                    <span class="status-badge status-{{ $assignment->status === 'accepted' ? 'active' : 'muted' }}">{{ $assignment->status }}</span>
                                </div>
                                <form method="POST" action="{{ route('tasks.unassign', [$task, $assignment]) }}" class="form-remove-assignment">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" aria-label="Remove assignment">
                                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-muted small mb-0" id="assignments-empty">Not assigned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Task Snapshot Info -->
            <div class="card shadow-sm mb-3 reveal" style="--d: 180ms">
                <div class="card-header mono"><i class="bi bi-info-circle me-1 text-accent"></i>Snapshot</div>
                <ul class="list-unstyled small mb-0 p-3">
                    <li><span class="text-muted">Location:</span> {{ $task->property_name_snapshot ?? '—' }}</li>
                    <li><span class="text-muted">Address:</span> {{ $task->address_snapshot ?? '—' }}</li>
                    <li class="mt-1">@include('partials.directions-button', ['task' => $task])</li>
                    <li><span class="text-muted">Radius:</span> {{ $task->check_in_radius_snapshot ? $task->check_in_radius_snapshot.' m' : '—' }}</li>
                    <li><span class="text-muted">Approval:</span> {{ $task->approval_required ? 'required' : 'not required' }}</li>
                    <li><span class="text-muted">Recurrence:</span> {{ $task->recurrence_rule ?? 'none' }}</li>
                </ul>
            </div>

            @if(auth()->user()->hasPermission('4.4'))
                @include('partials.evidence-upload', ['task' => $task])
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>@include('partials.evidence-upload-js', ['task' => $task])</script>
    <script>
        (function ($) {
            var toastTimer = null;
            function showToast(message, type) {
                var $toast = $('#ajax-toast-banner');
                var $icon = $('#ajax-toast-icon');
                var $text = $('#ajax-toast-text');

                $toast.removeClass('alert-success alert-danger alert-info');
                if (type === 'error') {
                    $toast.addClass('alert-danger');
                    $icon.attr('class', 'bi bi-exclamation-octagon-fill fs-5 text-danger');
                } else if (type === 'loading') {
                    $toast.addClass('alert-info');
                    $icon.attr('class', 'spinner-border spinner-border-sm text-info');
                } else {
                    $toast.addClass('alert-success');
                    $icon.attr('class', 'bi bi-check-circle-fill fs-5 text-success');
                }

                $text.text(message);
                $toast.fadeIn(200);

                if (toastTimer) clearTimeout(toastTimer);
                if (type !== 'loading') {
                    toastTimer = setTimeout(function () {
                        $toast.fadeOut(300);
                    }, 2500);
                }
            }

            // 1. Task Details Form Submit via Axios
            $('#form-task-details').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                showToast('Saving task details…', 'loading');

                axios.put($form.attr('action'), $form.serialize())
                    .then(function (res) {
                        showToast(res.data.message || 'Task details saved successfully!', 'success');
                        if ($('#title').val()) {
                            $('#task-header-title').text($('#title').val());
                        }
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error saving task details.', 'error');
                    });
            });

            // 2. Add Subtask via Axios
            $('#form-add-subtask').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var title = $('#new-subtask-title').val().trim();
                if (!title) return;

                showToast('Adding subtask…', 'loading');

                axios.post($form.attr('action'), $form.serialize())
                    .then(function (res) {
                        showToast('Subtask added!', 'success');
                        $('#new-subtask-title').val('');
                        $('#subtasks-empty').hide();

                        var st = res.data.subtask;
                        var toggleUrl = '{{ route('tasks.subtasks.toggle', [$task, ':subtaskId']) }}'.replace(':subtaskId', st.id);
                        
                        var html = '<div class="d-flex justify-content-between align-items-center py-2 border-bottom subtask-item-row" data-subtask-id="' + st.id + '">' +
                            '<span class="small subtask-title-text">' +
                            '<i class="bi bi-circle text-muted me-1 subtask-icon"></i>' + $('<div>').text(st.title).html() +
                            '</span>' +
                            '<button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-toggle-subtask text-success" data-url="' + toggleUrl + '">Done</button>' +
                            '</div>';
                        $('#subtasks-list-container').append(html);

                        var currentCount = parseInt($('#subtasks-count').text()) || 0;
                        $('#subtasks-count').text(currentCount + 1);
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error adding subtask.', 'error');
                    });
            });

            // 3. Toggle Subtask via Axios
            $(document).on('click', '.btn-toggle-subtask', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var url = $btn.data('url');
                var $row = $btn.closest('.subtask-item-row');
                var $title = $row.find('.subtask-title-text');
                var $icon = $row.find('.subtask-icon');

                showToast('Updating subtask…', 'loading');

                axios.post(url)
                    .then(function (res) {
                        var isCompleted = res.data.completed;
                        if (isCompleted) {
                            $title.addClass('completed-subtask');
                            $icon.attr('class', 'bi bi-check2-circle text-success me-1 subtask-icon');
                            $btn.text('Reopen').removeClass('text-success').addClass('text-muted');
                            showToast('Subtask completed!', 'success');
                        } else {
                            $title.removeClass('completed-subtask');
                            $icon.attr('class', 'bi bi-circle text-muted me-1 subtask-icon');
                            $btn.text('Done').removeClass('text-muted').addClass('text-success');
                            showToast('Subtask reopened!', 'success');
                        }
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error updating subtask.', 'error');
                    });
            });

            // 4. Move Status via Axios
            $('#form-move-status').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                showToast('Updating task status…', 'loading');

                axios.post($form.attr('action'), $form.serialize())
                    .then(function (res) {
                        var data = res.data;
                        showToast(data.message || 'Status updated!', 'success');

                        // Update badges
                        var statusText = data.formatted_status;
                        $('#task-status-badge').text(statusText);
                        $('#status-card-header-badge').text(statusText);

                        // Update select dropdown options
                        var $select = $('#status');
                        $select.empty();
                        (data.transitionable_statuses || []).forEach(function (s) {
                            var label = s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ');
                            $select.append(new Option(label, s));
                        });
                        $('#remarks').val('');

                        // Append to history table
                        if (data.history_entry) {
                            $('#history-empty-row').remove();
                            var h = data.history_entry;
                            var rowHtml = '<tr>' +
                                '<td class="small">' + (h.previous_status || '—').replace('_', ' ') + '</td>' +
                                '<td class="small"><span class="status-badge status-muted">' + (h.new_status || '').replace('_', ' ') + '</span></td>' +
                                '<td class="small">' + $('<div>').text(h.user_name).html() + '</td>' +
                                '<td class="small text-muted">' + h.created_at + '</td>' +
                                '<td class="small text-muted">' + $('<div>').text(h.remarks).html() + '</td>' +
                                '</tr>';
                            $('#history-table-body').prepend(rowHtml);
                        }
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error updating status.', 'error');
                    });
            });

            // 5. Add Assignee via Axios
            $('#form-add-assignee').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                showToast('Adding assignee…', 'loading');

                axios.post($form.attr('action'), $form.serialize())
                    .then(function (res) {
                        var data = res.data;
                        showToast(data.message || 'Assignee added!', 'success');
                        $('#assignments-empty').hide();

                        var a = data.assignment;
                        var rowHtml = '<div class="d-flex justify-content-between align-items-center py-1 border-bottom assignment-row-item" data-assignment-id="' + a.id + '">' +
                            '<div>' +
                            '<span class="fw-semibold small">' + $('<div>').text(a.assignee_name).html() + '</span>' +
                            '<span class="status-badge status-muted ms-2">' + a.assignee_type + '</span>' +
                            '<span class="status-badge status-' + (a.status === 'accepted' ? 'active' : 'muted') + ' ms-1">' + a.status + '</span>' +
                            '</div>' +
                            '<form method="POST" action="' + a.delete_url + '" class="form-remove-assignment">' +
                            '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="submit" class="btn btn-sm btn-link text-danger p-0" aria-label="Remove assignment">' +
                            '<i class="bi bi-x-circle" aria-hidden="true"></i>' +
                            '</button>' +
                            '</form>' +
                            '</div>';

                        $('#assignments-list-container').append(rowHtml);
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error adding assignee.', 'error');
                    });
            });

            // 6. Remove Assignee via Axios
            $(document).on('submit', '.form-remove-assignment', function (e) {
                e.preventDefault();
                if (!confirm('Remove assignment?')) return;

                var $form = $(this);
                var $row = $form.closest('.assignment-row-item');
                showToast('Removing assignment…', 'loading');

                axios.delete($form.attr('action'))
                    .then(function (res) {
                        showToast('Assignment removed.', 'success');
                        $row.fadeOut(200, function () { $(this).remove(); });
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error removing assignment.', 'error');
                    });
            });
        })(jQuery);
    </script>
@endpush
