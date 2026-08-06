@extends('layouts.app')

@section('title', 'New Task')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container .select2-selection--single, .select2-container .select2-selection--multiple { min-height: 36px; border-color: var(--cw-border-strong); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 34px; }
        .form-section-label { font-family: var(--cw-font-mono); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--cw-accent-deep); margin-bottom: 0.35rem; }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('tasks.store') }}" class="reveal">
        @csrf

        <!-- Clean Page Header (Actions at Bottom Only) -->
        <div class="mb-2">
            <span class="eyebrow">Tasks · Create</span>
            <h1 class="h4 mt-1 mb-0 font-weight-bold">Schedule New Task</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-2" role="alert"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}</div>
        @endif

        <!-- High-Density Dual-Panel Form Grid (7 / 5 Split with Minimal Gaps) -->
        <div class="row g-2">
            <!-- Left Column: Property, Schedule, People (7 Cols) -->
            <div class="col-lg-7">
                <!-- Panel 1: Property & Location Snapshots -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-building me-1 text-accent"></i>1 · Property & Location</span>
                        @if(auth()->user()->hasPermission('3.2'))
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="quick-property-toggle" style="font-size: 11px;">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Property
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="property_id" class="form-label mb-0">Property <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-link p-0 extra-small text-decoration-none mono" data-bs-toggle="collapse" data-bs-target="#location-snapshot-drawer">
                                        Edit Snapshots <i class="bi bi-pencil-square ms-1"></i>
                                    </button>
                                </div>
                                <select name="property_id" id="property_id" class="form-select" required>
                                    <option value="">Search or pick a property…</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)
                                            data-address="{{ $property->formatted_address ?: $property->address }}"
                                            data-lat="{{ $property->latitude }}"
                                            data-lng="{{ $property->longitude }}">{{ $property->name }} — {{ $property->formatted_address ?: $property->address }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 extra-small text-muted" id="location-snapshot-summary" style="display: none;"></div>

                            <!-- Collapsible Location Snapshot Drawer -->
                            <div class="collapse col-12 mt-1" id="location-snapshot-drawer">
                                <div class="p-2 border rounded bg-light row g-2">
                                    <div class="col-md-6">
                                        <label for="property_name_snapshot" class="form-label extra-small mb-1">Location Name</label>
                                        <input type="text" id="property_name_snapshot" name="property_name_snapshot" value="{{ old('property_name_snapshot') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address_snapshot" class="form-label extra-small mb-1">Address</label>
                                        <input type="text" id="address_snapshot" name="address_snapshot" value="{{ old('address_snapshot') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label for="latitude_snapshot" class="form-label extra-small mb-1">Latitude</label>
                                        <input type="number" step="any" id="latitude_snapshot" name="latitude_snapshot" value="{{ old('latitude_snapshot') }}" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label for="longitude_snapshot" class="form-label extra-small mb-1">Longitude</label>
                                        <input type="number" step="any" id="longitude_snapshot" name="longitude_snapshot" value="{{ old('longitude_snapshot') }}" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Schedule, Type & Priority Matrix -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3"><i class="bi bi-calendar-event me-1 text-accent"></i>2 · Schedule & Type</div>
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="task_type_id" class="form-label mb-1">Task Type</label>
                                <select name="task_type_id" id="task_type_id" class="form-select form-select-sm">
                                    <option value="">None</option>
                                    @foreach ($taskTypes as $type)
                                        <option value="{{ $type->id }}" @selected(old('task_type_id') == $type->id)
                                            data-duration="{{ $type->default_estimated_duration_minutes }}"
                                            data-approval="{{ $type->approval_required ? 1 : 0 }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="estimated_duration_minutes" class="form-label mb-1">Duration (min)</label>
                                <input type="number" min="1" max="1440" id="estimated_duration_minutes" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes') }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="priority" class="form-label mb-1">Priority</label>
                                <select name="priority" id="priority" class="form-select form-select-sm">
                                    @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                        <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Starts At & Ends At Side-by-Side on Mobile (col-6 col-md-6) -->
                            <div class="col-6 col-md-6">
                                <label for="scheduled_start_at" class="form-label mb-1">Starts At <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="scheduled_start_at" name="scheduled_start_at" value="{{ old('scheduled_start_at') }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6 col-md-6">
                                <label for="scheduled_end_at" class="form-label mb-1">Ends At</label>
                                <input type="datetime-local" id="scheduled_end_at" name="scheduled_end_at" value="{{ old('scheduled_end_at') }}" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: People & Team Assignments -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3"><i class="bi bi-people me-1 text-accent"></i>3 · People & Assignments</div>
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label for="assignee_ids" class="form-label mb-1">Assignees (multiple)</label>
                                <select name="assignee_ids[]" id="assignee_ids" class="form-select" multiple>
                                    @foreach ($cleaners->concat($managers) as $person)
                                        <option value="{{ $person->id }}" @selected(in_array($person->id, old('assignee_ids', [])))>{{ $person->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="assigned_manager_id" class="form-label mb-1">Manager</label>
                                <select name="assigned_manager_id" id="assigned_manager_id" class="form-select form-select-sm mb-1.5">
                                    <option value="">None</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}" @selected(old('assigned_manager_id') == $manager->id)>{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                                <label for="team_id" class="form-label mb-1">Team (optional)</label>
                                <select name="team_id" id="team_id" class="form-select form-select-sm">
                                    <option value="">No team</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-1.5 pt-1.5 border-top d-flex justify-content-between align-items-center">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="approval_required" value="1" id="approval_required" @checked($errors->any() ? old('approval_required') : true)>
                                    <label class="form-check-label small fw-semibold" for="approval_required">Require Supervisor Approval</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Checklists, Subtasks, Recurrence (5 Cols) -->
            <div class="col-lg-5">
                <!-- Panel 4: Checklist & Dynamic Sub-Tasks (Collapsible & Hidden by Default, Saved in DB) -->
                @php $hasChecklistContent = old('checklist_template_id') || old('subtasks') || ($checklistEnabled ?? false); @endphp
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                            <input class="form-check-input" type="checkbox" id="checklist-enabled" @checked($hasChecklistContent)>
                            <label class="form-check-label fw-bold" for="checklist-enabled">
                                <i class="bi bi-check2-square me-1 text-accent"></i>4 · Checklist & Subtasks (Optional)
                            </label>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="add-subtask" style="font-size: 11px; {{ $hasChecklistContent ? '' : 'display: none;' }}">
                            <i class="bi bi-plus me-1"></i>Add Subtask
                        </button>
                    </div>
                    <div id="checklist-fields" class="card-body p-2.5 px-3" style="{{ $hasChecklistContent ? '' : 'display: none;' }}">
                        <div class="mb-2">
                            <label for="checklist_template_id" class="form-label mb-1">Checklist Template</label>
                            <select name="checklist_template_id" id="checklist_template_id" class="form-select form-select-sm">
                                <option value="">From task type (or none)</option>
                                @foreach ($checklists as $checklist)
                                    <option value="{{ $checklist->id }}" @selected(old('checklist_template_id') == $checklist->id)>{{ $checklist->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-section-label mt-2 mb-1">Dynamic Sub-Tasks</div>
                        <div id="subtask-rows" class="d-flex flex-column gap-1">
                            @foreach (old('subtasks', []) as $index => $subtask)
                                <div class="input-group input-group-sm subtask-row">
                                    <input type="text" name="subtasks[{{ $index }}][title]" value="{{ $subtask['title'] }}" class="form-control" placeholder="Sub task title…">
                                    <button type="button" class="btn btn-outline-danger remove-subtask"><i class="bi bi-x" aria-hidden="true"></i></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Panel 5: Recurrence & Overrides -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3">
                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                            <input class="form-check-input" type="checkbox" id="recurrence-enabled" @checked(old('recurrence_rule'))>
                            <label class="form-check-label fw-bold" for="recurrence-enabled">
                                <i class="bi bi-arrow-repeat me-1 text-accent"></i>5 · Recurrence (Optional)
                            </label>
                        </div>
                    </div>
                    <div id="recurrence-fields" class="card-body p-2.5 px-3" style="display: none;">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="recurrence_rule" class="form-label extra-small mb-1">RRULE Expression</label>
                                <input type="text" id="recurrence_rule" name="recurrence_rule" value="{{ old('recurrence_rule') }}" class="form-control form-control-sm mono" placeholder="FREQ=WEEKLY;INTERVAL=1">
                                <div class="form-text extra-small">Leave blank for a single one-off task.</div>
                            </div>
                            <div class="col-12 mt-1.5 pt-1.5 border-top">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" name="override_warnings" value="1" id="override_warnings">
                                    <label class="form-check-label extra-small fw-semibold" for="override_warnings">Allow Availability Conflict Warnings</label>
                                </div>
                                <input type="text" name="override_reason" value="{{ old('override_reason') }}" class="form-control form-control-sm mt-1" placeholder="Override reason (if flagged)">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions ONLY: Cancel & Create Task Side-by-Side on Same Line -->
                <div class="d-flex gap-2 w-100 mt-3">
                    <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-touch flex-fill">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-touch flex-fill fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Create Task
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Quick Add Property Modal -->
    @if(auth()->user()->hasPermission('3.2'))
        <div class="modal fade" id="quickPropertyModal" tabindex="-1" aria-labelledby="quickPropertyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title h6 font-weight-bold" id="quickPropertyModalLabel"><i class="bi bi-building-add me-1 text-accent"></i>Add Property</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="qp-name" class="form-label">Property Name <span class="text-danger">*</span></label>
                                <input type="text" id="qp-name" class="form-control form-control-sm" placeholder="e.g. Harbourview Offices" required>
                            </div>
                            <div class="col-12">
                                <label for="qp-address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" id="qp-address" class="form-control form-control-sm" placeholder="e.g. 1 Queen Street, Auckland" required>
                            </div>
                            <div class="col-12">
                                <label for="qp-category" class="form-label">Category</label>
                                <select id="qp-category" class="form-select form-select-sm">
                                    <option value="">None</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-muted extra-small" id="qp-status"></div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm fw-bold" id="qp-save">
                            <i class="bi bi-building-add me-1" aria-hidden="true"></i>Save & Select
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function ($) {
            // Property cache
            var propCache = {};
            $('#property_id option').each(function () {
                if (this.value) propCache[this.value] = {
                    name: this.text.split(' — ')[0],
                    address: this.dataset.address || '',
                    lat: this.dataset.lat || '',
                    lng: this.dataset.lng || ''
                };
            });

            // Property Select2
            var $property = $('#property_id').select2({
                placeholder: 'Search or pick a property…',
                allowClear: true,
                ajax: {
                    url: '{{ route('properties.options') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term || '' }; },
                    processResults: function (res) {
                        (res.results || []).forEach(function (r) {
                            propCache[r.id] = { name: r.text.split(' — ')[0], address: r.address, lat: r.latitude, lng: r.longitude };
                        });
                        return { results: res.results };
                    }
                }
            });

            function autofill(id) {
                var p = propCache[id] || { name: '', address: '', lat: '', lng: '' };
                $('#property_name_snapshot').val(p.name);
                $('#address_snapshot').val(p.address);
                $('#latitude_snapshot').val(p.lat);
                $('#longitude_snapshot').val(p.lng);

                if (p.name || p.address) {
                    $('#location-snapshot-summary').html('<i class="bi bi-geo-alt-fill text-success me-1"></i><strong>' + p.name + '</strong> — ' + p.address).show();
                } else {
                    $('#location-snapshot-summary').hide();
                }
            }

            $property.on('change', function () {
                var selected = $property.select2('data')[0];
                autofill(selected ? selected.id : '');
            });

            // Quick property modal hash handling
            var $qpModal = $('#quickPropertyModal');

            $('#quick-property-toggle').on('click', function () {
                window.location.hash = 'add-property';
            });

            $(window).on('hashchange', function () {
                if (window.location.hash === '#add-property') {
                    $qpModal.modal('show');
                } else {
                    $qpModal.modal('hide');
                }
            });

            $qpModal.on('hidden.bs.modal', function () {
                if (window.location.hash === '#add-property') {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                }
            });

            if (window.location.hash === '#add-property') {
                $qpModal.modal('show');
            }

            $('#qp-save').on('click', function () {
                var name = $('#qp-name').val().trim(), address = $('#qp-address').val().trim();
                if (!name || !address) { $('#qp-status').text('Name and address are required.'); return; }
                $('#qp-status').text('Saving…');
                axios.post('{{ route('properties.store') }}', {
                    name: name,
                    address: address,
                    property_category_id: $('#qp-category').val() || null
                })
                    .then(function () {
                        $('#qp-status').text('Saved — selecting it now…');
                        axios.get('{{ route('properties.options') }}', { params: { q: name } })
                            .then(function (res) {
                                var found = (res.data.results || []).find(function (r) {
                                    return r.text.indexOf(name) === 0 || r.text.indexOf(' — ' + address) > -1;
                                });
                                if (found) {
                                    propCache[found.id] = { name: name, address: found.address, lat: found.latitude, lng: found.longitude };
                                    var opt = new Option(found.text, found.id, true, true);
                                    $property.append(opt).trigger('change');
                                } else {
                                    $property.val('').trigger('change');
                                }
                            })
                            .catch(function () { $property.val('').trigger('change'); })
                            .finally(function () {
                                $('#qp-name').val(''); $('#qp-address').val(''); $('#qp-category').val('');
                                $qpModal.modal('hide');
                            });
                    })
                    .catch(function (err) {
                        $('#qp-status').text(err.response?.data?.message || err.response?.data?.errors?.address?.[0] || 'Save failed.');
                    });
            });

            // Dynamic subtasks
            function addSubtaskRow() {
                var idx = $('#subtask-rows .subtask-row').length;
                $('<div class="input-group input-group-sm subtask-row">' +
                    '<input type="text" name="subtasks[' + idx + '][title]" class="form-control" placeholder="Sub task title…">' +
                    '<button type="button" class="btn btn-outline-danger remove-subtask"><i class="bi bi-x" aria-hidden="true"></i></button>' +
                    '</div>').appendTo('#subtask-rows');
            }
            $('#add-subtask').on('click', addSubtaskRow);
            $(document).on('click', '.remove-subtask', function () { $(this).closest('.subtask-row').remove(); });
            if (!$('#subtask-rows .subtask-row').length) addSubtaskRow();

            // Checklist & Subtasks toggle with DB persistence via AJAX
            $('#checklist-enabled').on('change', function () {
                var isChecked = this.checked;
                $('#checklist-fields').toggle(isChecked);
                $('#add-subtask').toggle(isChecked);
                axios.post('{{ route('user-preferences.store') }}', {
                    key: 'ui_checklist_enabled',
                    value: isChecked ? '1' : '0'
                });
            });

            // Recurrence toggle
            $('#recurrence-enabled').on('change', function () {
                $('#recurrence-fields').toggle(this.checked);
                if (this.checked && !$('#recurrence_rule').val()) {
                    $('#recurrence_rule').val('FREQ=WEEKLY;INTERVAL=1');
                }
            });

            // Task type approval requirement sync (no auto duration population as requested)
            $('#task_type_id').on('change', function () {
                var opt = $(this).find(':selected');
                if (opt.data('approval')) {
                    $('#approval_required').prop('checked', true);
                } else {
                    $('#approval_required').prop('checked', false);
                }
            });
        })(jQuery);
    </script>
@endpush
