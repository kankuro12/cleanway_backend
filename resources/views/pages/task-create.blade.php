@extends('layouts.app')

@section('title', 'New Task')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container .select2-selection--single, .select2-container .select2-selection--multiple { min-height: 36px; border-color: var(--cw-border-strong); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 34px; }
        .form-section-label { font-family: var(--cw-font-mono); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--cw-accent-deep); margin-bottom: 0.35rem; }

        /* Space-saving Assignee Pill */
        .assignee-pill {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 8px 3px 8px;
            font-size: 0.78rem;
            transition: all 0.15s ease;
        }
        .assignee-pill:hover {
            border-color: #cbd5e1;
            background-color: #f1f5f9;
        }
        .assignee-avatar-xs {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #cbd5e1;
            color: #1e293b;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .btn-remove-pill {
            width: 18px;
            height: 18px;
            border: none;
            background: transparent;
            color: #94a3b8;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin-left: 2px;
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-remove-pill:hover {
            background-color: #fee2e2;
            color: #ef4444;
        }

        /* User selection modal list */
        .user-select-row {
            cursor: pointer;
            transition: background 0.15s ease;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .user-select-row:hover {
            background-color: #f8fafc;
        }
        .user-avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            background-color: #e2e8f0;
            color: #334155;
            flex-shrink: 0;
        }

        /* Universal Sticky Bottom Save Bar */
        .sticky-bottom-bar {
            position: fixed !important;
            bottom: 0 !important;
            left: 240px !important;
            right: 0 !important;
            z-index: 1045 !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border-top: 1px solid #cbd5e1 !important;
            padding: 10px 24px !important;
            margin: 0 !important;
            box-shadow: 0 -4px 18px rgba(0,0,0,0.08) !important;
        }

        .task-form-wrapper {
            padding-bottom: 76px !important;
        }

        /* Compact mobile inputs & small font */
        @media (max-width: 991.98px) {
            .mobile-bottom-nav {
                display: none !important;
            }
            .sticky-bottom-bar {
                left: 0 !important;
                padding: 10px 14px !important;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.15) !important;
            }
            .form-control, .form-select, .select2-container--default .select2-selection--single, .select2-container--default .select2-selection--multiple {
                font-size: 0.8125rem !important;
                padding: 3px 8px !important;
                min-height: 30px !important;
                height: 30px !important;
            }
            .form-control-sm, .form-select-sm {
                font-size: 0.75rem !important;
                padding: 2px 6px !important;
                min-height: 28px !important;
                height: 28px !important;
            }
            .form-label {
                font-size: 0.75rem !important;
                margin-bottom: 0.15rem !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 24px !important;
                font-size: 0.8125rem !important;
                padding-left: 2px !important;
            }
            .card-header {
                padding-top: 4px !important;
                padding-bottom: 4px !important;
                font-size: 0.75rem !important;
            }
            .card-body {
                padding: 6px 8px !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="task-form-wrapper">
    <form method="POST" action="{{ route('tasks.store') }}" id="form-task-create">
        @csrf

        <!-- Clean Page Header (Actions at Bottom Only) -->
        <div class="mb-2">
            <span class="eyebrow">Tasks · Create</span>
            <h1 class="h3 mt-1 mb-0">Schedule New Task</h1>
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
                                <label for="duration_hours" class="form-label mb-1">Hours</label>
                                <input type="number" min="0" max="24" id="duration_hours" name="duration_hours" value="{{ old('duration_hours', 1) }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="duration_minutes" class="form-label mb-1">Minutes</label>
                                <input type="number" min="0" max="59" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 20) }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="priority" class="form-label mb-1">Priority</label>
                                <select name="priority" id="priority" class="form-select form-select-sm">
                                    @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                        <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="hourly_rate" class="form-label mb-1">Rate Per Hour ($)</label>
                                <input type="number" step="0.01" min="0" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}" class="form-control form-control-sm" placeholder="25.00">
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

                <!-- Financials & Extra Payments Panel -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-cash-stack me-1 text-accent"></i>Financials & Parking</span>
                    </div>
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="parking_fee" class="form-label mb-1">Extra Parking Default Money ($)</label>
                                <input type="number" step="0.01" min="0" id="parking_fee" name="parking_fee" value="{{ old('parking_fee', '0.00') }}" class="form-control form-control-sm" placeholder="0.00">
                                <div class="form-text extra-small">Editable default parking fee for cleaner task.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: People & Team Assignments -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-people me-1 text-accent"></i>3 · People & Assignments
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" id="assignments-count-badge">0</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-0 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; height: 26px;" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                            <i class="bi bi-plus-lg"></i>Assign Cleaners & Personnel
                        </button>
                    </div>
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <div id="assignments-list-container" class="d-flex flex-wrap align-items-center gap-2 p-2 border rounded bg-light" style="min-height: 38px;">
                                    @php
                                        $oldAssigneeIds = old('assignee_ids', []);
                                        $userMap = ($people ?? $cleaners->concat($managers) ?? collect())->keyBy('id');
                                    @endphp
                                    @forelse ($oldAssigneeIds as $uId)
                                        @php $userObj = $userMap->get($uId); @endphp
                                        @if($userObj)
                                            <div class="assignee-pill d-inline-flex align-items-center gap-2 rounded-pill assignment-row-item" data-user-id="{{ $userObj->id }}">
                                                <div class="assignee-avatar-xs">{{ strtoupper(substr($userObj->name, 0, 1)) }}</div>
                                                <span class="fw-semibold extra-small text-dark text-truncate" style="max-width: 140px;">{{ $userObj->name }}</span>
                                                <input type="hidden" name="assignee_ids[]" value="{{ $userObj->id }}">
                                                <button type="button" class="btn-remove-pill" aria-label="Remove {{ $userObj->name }}">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @endif
                                    @empty
                                        <span class="text-muted extra-small" id="assignments-empty">No assignees selected yet.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="assigned_manager_id" class="form-label mb-1">Supervisor</label>
                                <select name="assigned_manager_id" id="assigned_manager_id" class="form-select form-select-sm">
                                    <option value="">None</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}" @selected(old('assigned_manager_id') == $manager->id)>{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="team_id" class="form-label mb-1">Team</label>
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
                <!-- Panel 4: Checklist → Subtasks -->
                @php $hasChecklistContent = true; @endphp
                <div class="card shadow-sm mb-2">
                    <div class="card-header mono py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="form-check form-switch d-flex align-items-center gap-2 m-0">
                            <input class="form-check-input" type="checkbox" id="checklist-enabled" @checked($hasChecklistContent)>
                            <label class="form-check-label fw-bold" for="checklist-enabled">
                                <i class="bi bi-check2-square me-1 text-accent"></i>4 · Checklist → Subtasks
                            </label>
                        </div>
                    </div>
                    <div id="checklist-fields" class="card-body p-2.5 px-3" style="{{ $hasChecklistContent ? '' : 'display: none;' }}">
                        <div class="mb-1">
                            <label for="checklist_template_id" class="form-label mb-1">Checklist Template</label>
                            <select name="checklist_template_id" id="checklist_template_id" class="form-select form-select-sm">
                                <option value="">None</option>
                                @foreach ($checklists as $checklist)
                                    <option value="{{ $checklist->id }}" @selected(old('checklist_template_id') == $checklist->id)>{{ $checklist->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="checklist-preview" class="border rounded p-2 bg-light mt-2" style="max-height:240px;overflow:auto;{{ old('checklist_template_id') ? '' : 'display:none;' }}"></div>
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
            </div>
        </div>
    </form>

    <!-- Sticky Bottom Bar (Fixed to Viewport Bottom) -->
    <div class="sticky-bottom-bar d-flex align-items-center justify-content-end gap-2">
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm px-3 flex-fill flex-md-grow-0">Cancel</a>
        <button type="submit" form="form-task-create" class="btn btn-primary btn-sm fw-bold px-4 flex-fill flex-md-grow-0">
            <i class="bi bi-check2-circle me-1"></i>Create Task
        </button>
    </div>
</div>

    <!-- Quick Add Property Modal -->
    @if(auth()->user()->hasPermission('3.2'))
        <div class="modal fade" id="quickPropertyModal" tabindex="-1" aria-labelledby="quickPropertyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h5 class="modal-title h6 fw-bold" id="quickPropertyModalLabel"><i class="bi bi-building-add me-1 text-accent"></i>Add Property</h5>
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

    <!-- Assign User Modal with Search & Checkboxes -->
    <div class="modal fade" id="assignUserModal" tabindex="-1" aria-labelledby="assignUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom py-2 px-3">
                    <h5 class="modal-title h6 fw-bold mb-0 text-dark" id="assignUserModalLabel">
                        <i class="bi bi-person-plus-fill me-1 text-primary"></i>Assign Cleaners & Personnel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Search input -->
                    <div class="mb-3 position-relative">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="assignee-search-input" class="form-control border-start-0" placeholder="Search user by name or role…">
                            <button type="button" class="btn btn-outline-secondary d-none" id="clear-search-btn"><i class="bi bi-x"></i></button>
                        </div>
                    </div>

                    <!-- User Selection List -->
                    @php
                        $userList = $people ?? $cleaners->concat($managers) ?? [];
                    @endphp
                    <div class="user-select-list d-flex flex-column gap-1" id="assignee-user-list">
                        @forelse($userList as $u)
                            <label class="user-select-row d-flex align-items-center justify-content-between p-2 rounded" data-user-name="{{ strtolower($u->name) }}" data-user-role="{{ strtolower($u->role ?? '') }}" data-user-id="{{ $u->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input user-select-checkbox m-0" value="{{ $u->id }}" data-user-name="{{ $u->name }}">
                                    <div class="user-avatar-circle">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold small text-dark mb-0">{{ $u->name }}</div>
                                        <span class="extra-small text-muted text-uppercase mono">{{ $u->role ?? 'cleaner' }}</span>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <p class="text-muted small text-center py-3">No users found.</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 justify-content-between flex-wrap gap-2">
                    <span class="extra-small text-muted" id="selected-assignees-count">0 selected</span>
                    <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1 fw-semibold flex-fill flex-sm-grow-0" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary px-3 py-1 fw-bold flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center" id="btn-save-assignments">
                            <i class="bi bi-check2 me-1"></i>Add Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

            function loadChecklistPreview(id){
                var $preview=$('#checklist-preview');
                if(!id){
                    $preview.hide().empty();
                    return;
                }
                $preview.show().html('<p class="text-muted extra-small mb-0">Loading preview…</p>');
                axios.get('{{ route('checklists.items', '__ID__') }}'.replace('__ID__',id)).then(function(res){
                    var sections=res.data.sections||[];
                    if(!sections.length){ $preview.html('<p class="text-muted extra-small mb-0">Empty checklist.</p>'); return; }
                    var html='';
                    sections.forEach(function(sec){
                        html+='<div class="fw-bold extra-small mt-1 text-dark">'+sec.name+'</div>';
                        (sec.items||[]).forEach(function(item){
                            var badges='';
                            if(item.is_photo_required) badges+=' <span class="badge bg-warning text-dark" style="font-size:9px">photo</span>';
                            if(item.is_comment_required) badges+=' <span class="badge bg-info" style="font-size:9px">comment</span>';
                            html+='<div class="extra-small ps-2 text-muted">• '+ $('<div>').text(item.label).html() + badges + '</div>';
                        });
                    });
                    $preview.html(html);
                }).catch(function(){ $preview.html('<p class="text-danger extra-small mb-0">Failed to load.</p>'); });
            }
            $('#checklist_template_id').on('change',function(){ loadChecklistPreview($(this).val()); });
            // init preview if preselected
            if($('#checklist_template_id').val()) loadChecklistPreview($('#checklist_template_id').val());

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

            // Checklist & Requirements toggle with DB persistence via AJAX
            $('#checklist-enabled').on('change', function () {
                var isChecked = this.checked;
                $('#checklist-fields').toggle(isChecked);
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

            // Assignee Pills & Modal Management
            function getCurrentlyAssignedIds() {
                var ids = [];
                $('#assignments-list-container .assignment-row-item').each(function() {
                    var uid = parseInt($(this).data('user-id'), 10);
                    if (uid) ids.push(uid);
                });
                return ids;
            }

            function updateAssignmentsBadge() {
                var count = $('#assignments-list-container .assignment-row-item').length;
                $('#assignments-count-badge').text(count);
                if (count === 0) {
                    if (!$('#assignments-empty').length) {
                        $('#assignments-list-container').html('<span class="text-muted extra-small" id="assignments-empty">No assignees selected yet.</span>');
                    }
                } else {
                    $('#assignments-empty').remove();
                }
            }

            updateAssignmentsBadge();

            // When modal opens: uncheck all, hide already assigned users from list
            $('#assignUserModal').on('show.bs.modal', function () {
                $('#assignee-search-input').val('');
                $('#clear-search-btn').addClass('d-none');
                $('#assignee-user-list .user-select-checkbox').prop('checked', false);
                
                var assignedIds = getCurrentlyAssignedIds();
                $('#assignee-user-list .user-select-row').each(function() {
                    var uid = parseInt($(this).data('user-id'), 10);
                    if (assignedIds.includes(uid)) {
                        $(this).addClass('d-none');
                    } else {
                        $(this).removeClass('d-none');
                    }
                });
                updateSelectedAssigneesCount();
            });

            $('#assignee-search-input').on('input', function() {
                var q = $(this).val().toLowerCase().trim();
                $('#clear-search-btn').toggleClass('d-none', !q);
                var assignedIds = getCurrentlyAssignedIds();

                $('#assignee-user-list .user-select-row').each(function() {
                    var uid = parseInt($(this).data('user-id'), 10);
                    if (assignedIds.includes(uid)) {
                        $(this).addClass('d-none');
                        return;
                    }
                    var name = ($(this).data('user-name') || '').toString();
                    var role = ($(this).data('user-role') || '').toString();
                    if (name.indexOf(q) !== -1 || role.indexOf(q) !== -1) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            $('#clear-search-btn').on('click', function() {
                $('#assignee-search-input').val('').trigger('input');
            });

            function updateSelectedAssigneesCount() {
                var checkedCount = $('#assignee-user-list .user-select-checkbox:checked').length;
                $('#selected-assignees-count').text(checkedCount + ' selected');
                $('#btn-save-assignments').prop('disabled', checkedCount === 0);
            }

            $('#assignee-user-list').on('change', '.user-select-checkbox', function() {
                updateSelectedAssigneesCount();
            });

            // Add selected assignees as pills
            $('#btn-save-assignments').on('click', function() {
                var $checked = $('#assignee-user-list .user-select-checkbox:checked');
                if (!$checked.length) return;

                $('#assignments-empty').remove();

                $checked.each(function() {
                    var uid = $(this).val();
                    var name = $(this).data('user-name') || 'User';
                    var initial = name.charAt(0).toUpperCase();

                    var pillHtml = '<div class="assignee-pill d-inline-flex align-items-center gap-2 rounded-pill assignment-row-item" data-user-id="' + uid + '">' +
                        '<div class="assignee-avatar-xs">' + initial + '</div>' +
                        '<span class="fw-semibold extra-small text-dark text-truncate" style="max-width: 140px;">' + name + '</span>' +
                        '<input type="hidden" name="assignee_ids[]" value="' + uid + '">' +
                        '<button type="button" class="btn-remove-pill" aria-label="Remove ' + name + '"><i class="bi bi-x"></i></button>' +
                        '</div>';

                    $('#assignments-list-container').append(pillHtml);
                });

                updateAssignmentsBadge();
                $('#assignUserModal').modal('hide');
            });

            // Remove pill
            $(document).on('click', '#assignments-list-container .btn-remove-pill', function(e) {
                e.preventDefault();
                $(this).closest('.assignment-row-item').remove();
                updateAssignmentsBadge();
            });
        })(jQuery);
    </script>
@endpush
