@extends('layouts.app')

@section('title', 'New Task')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container .select2-selection--single, .select2-container .select2-selection--multiple { min-height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
    </style>
@endpush

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">Tasks · Create</span>
        <h2 class="h3 mt-1 mb-0">Schedule task</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('tasks.store') }}" class="reveal" style="--d: 80ms">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-header mono d-flex justify-content-between align-items-center">
                <span>1 · Property</span>
                @if(auth()->user()->hasPermission('3.2'))
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="quick-property-toggle">
                        <i class="bi bi-building-add me-1" aria-hidden="true"></i>Add property
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                        <select name="property_id" id="property_id" class="form-select" required>
                            <option value="">Search or pick a property…</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)
                                    data-address="{{ $property->formatted_address ?: $property->address }}"
                                    data-lat="{{ $property->latitude }}"
                                    data-lng="{{ $property->longitude }}">{{ $property->name }} — {{ $property->formatted_address ?: $property->address }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Title is auto-derived from the property — no manual title needed.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">2 · Location</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="property_name_snapshot" class="form-label">Location name</label>
                        <input type="text" id="property_name_snapshot" name="property_name_snapshot" value="{{ old('property_name_snapshot') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="address_snapshot" class="form-label">Address</label>
                        <input type="text" id="address_snapshot" name="address_snapshot" value="{{ old('address_snapshot') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="latitude_snapshot" class="form-label">Latitude</label>
                        <input type="number" step="any" id="latitude_snapshot" name="latitude_snapshot" value="{{ old('latitude_snapshot') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="longitude_snapshot" class="form-label">Longitude</label>
                        <input type="number" step="any" id="longitude_snapshot" name="longitude_snapshot" value="{{ old('longitude_snapshot') }}" class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Fills automatically from the selected property — override for one-off locations.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">3 · Schedule & type</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="task_type_id" class="form-label">Task type</label>
                        <select name="task_type_id" id="task_type_id" class="form-select">
                            <option value="">None</option>
                            @foreach ($taskTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('task_type_id') == $type->id)
                                    data-duration="{{ $type->default_estimated_duration_minutes }}"
                                    data-approval="{{ $type->approval_required ? 1 : 0 }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="scheduled_start_at" class="form-label">Starts at <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="scheduled_start_at" name="scheduled_start_at" value="{{ old('scheduled_start_at') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="scheduled_end_at" class="form-label">Ends at</label>
                        <input type="datetime-local" id="scheduled_end_at" name="scheduled_end_at" value="{{ old('scheduled_end_at') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="estimated_duration_minutes" class="form-label">Duration (min)</label>
                        <input type="number" min="1" max="1440" id="estimated_duration_minutes" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="assigned_manager_id" class="form-label">Manager</label>
                        <select name="assigned_manager_id" id="assigned_manager_id" class="form-select">
                            <option value="">None</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected(old('assigned_manager_id') == $manager->id)>{{ $manager->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="checklist_template_id" class="form-label">Checklist template</label>
                        <select name="checklist_template_id" id="checklist_template_id" class="form-select">
                            <option value="">From task type (or none)</option>
                            @foreach ($checklists as $checklist)
                                <option value="{{ $checklist->id }}" @selected(old('checklist_template_id') == $checklist->id)>{{ $checklist->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="approval_required" value="1" id="approval_required" @checked(old('approval_required'))>
                            <label class="form-check-label" for="approval_required">Approval required</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">4 · Assignees</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="assignee_ids" class="form-label">People (multiple allowed)</label>
                        <select name="assignee_ids[]" id="assignee_ids" class="form-select" multiple>
                            @foreach ($cleaners->concat($managers) as $person)
                                <option value="{{ $person->id }}" @selected(in_array($person->id, old('assignee_ids', [])))>{{ $person->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Searchable multi-select — pick one or more cleaners/supervisors.</div>
                    </div>
                    <div class="col-md-5">
                        <label for="team_id" class="form-label">Team (optional)</label>
                        <select name="team_id" id="team_id" class="form-select">
                            <option value="">No team</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">5 · Sub tasks</div>
            <div class="card-body">
                <div id="subtask-rows">
                    @foreach (old('subtasks', []) as $index => $subtask)
                        <div class="input-group input-group-sm mb-1 subtask-row">
                            <input type="text" name="subtasks[{{ $index }}][title]" value="{{ $subtask['title'] }}" class="form-control" placeholder="Sub task…">
                            <button type="button" class="btn btn-outline-danger remove-subtask"><i class="bi bi-x" aria-hidden="true"></i></button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-subtask">
                    <i class="bi bi-plus me-1" aria-hidden="true"></i>Add sub task
                </button>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="recurrence-enabled" @checked(old('recurrence_rule'))>
                    <label class="form-check-label" for="recurrence-enabled">5 · Recurrence (optional)</label>
                </div>
            </div>
            <div id="recurrence-fields" class="card-body" style="display: none;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="recurrence_rule" class="form-label">Rule</label>
                        <input type="text" id="recurrence_rule" name="recurrence_rule" value="{{ old('recurrence_rule') }}" class="form-control form-control-sm" placeholder="FREQ=WEEKLY;INTERVAL=1">
                        <div class="form-text">Leave blank for a one-time task.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Override warnings</label>
                        <div class="form-check form-switch d-inline-block me-3">
                            <input class="form-check-input" type="checkbox" name="override_warnings" value="1" id="override_warnings">
                            <label class="form-check-label small" for="override_warnings">Allow conflict/availability warnings</label>
                        </div>
                        <input type="text" name="override_reason" value="{{ old('override_reason') }}" class="form-control form-control-sm mt-2" placeholder="Recorded override reason">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2 me-1" aria-hidden="true"></i>Create task
        </button>
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>

    @if(auth()->user()->hasPermission('3.2'))
        <div class="modal fade" id="quickPropertyModal" tabindex="-1" aria-labelledby="quickPropertyModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickPropertyModalLabel">Add property</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="qp-name" class="form-label">Property name <span class="text-danger">*</span></label>
                                <input type="text" id="qp-name" class="form-control" placeholder="e.g. Harbourview Offices" required>
                            </div>
                            <div class="col-12">
                                <label for="qp-address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" id="qp-address" class="form-control" placeholder="e.g. 1 Queen Street, Auckland" required>
                            </div>
                            <div class="col-12">
                                <label for="qp-category" class="form-label">Category</label>
                                <select id="qp-category" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-muted small" id="qp-status"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" id="qp-save">
                            <i class="bi bi-building-add me-1" aria-hidden="true"></i>Save property
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
            // Property cache: id => {name, address, lat, lng} (static options + ajax results).
            var propCache = {};
            $('#property_id option').each(function () {
                if (this.value) propCache[this.value] = {
                    name: this.text.split(' — ')[0],
                    address: this.dataset.address || '',
                    lat: this.dataset.lat || '',
                    lng: this.dataset.lng || ''
                };
            });

            // Property: searchable select2 (server-side options) + autofill snapshots.
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
            }

            $property.on('change', function () {
                var selected = $property.select2('data')[0];
                autofill(selected ? selected.id : '');
            });

            // Quick-add property: #add-property hash opens the modal; browser
            // back (hash cleared) just closes it again.
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

            // Manual close (X / backdrop / Cancel): drop the hash without
            // leaving a history entry, so Back still exits the page.
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

            // Subtask dynamic rows.
            function addSubtaskRow() {
                var idx = $('#subtask-rows .subtask-row').length;
                $('<div class="input-group input-group-sm mb-1 subtask-row">' +
                    '<input type="text" name="subtasks[' + idx + '][title]" class="form-control" placeholder="Sub task…">' +
                    '<button type="button" class="btn btn-outline-danger remove-subtask"><i class="bi bi-x" aria-hidden="true"></i></button>' +
                    '</div>').appendTo('#subtask-rows');
            }
            $('#add-subtask').on('click', addSubtaskRow);
            $(document).on('click', '.remove-subtask', function () { $(this).closest('.subtask-row').remove(); });
            if (!$('#subtask-rows .subtask-row').length) addSubtaskRow();

            // Recurrence auto-hide.
            $('#recurrence-enabled').on('change', function () {
                $('#recurrence-fields').toggle(this.checked);
                if (this.checked && !$('#recurrence_rule').val()) {
                    $('#recurrence_rule').val('FREQ=WEEKLY;INTERVAL=1');
                }
            });

            $('#task_type_id').on('change', function () {
                var opt = $(this).find(':selected');
                if (opt.data('duration') && !$('#estimated_duration_minutes').val()) {
                    $('#estimated_duration_minutes').val(opt.data('duration'));
                }
                if (opt.data('approval')) {
                    $('#approval_required').prop('checked', true);
                }
            });
        })(jQuery);
    </script>
@endpush
