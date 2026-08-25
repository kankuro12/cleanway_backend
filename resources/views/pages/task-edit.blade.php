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
            display: none !important;
        }
        .toast-banner.show-toast {
            display: flex !important;
        }
        .completed-subtask {
            text-decoration: line-through;
            color: var(--cw-muted);
        }

        /* 8-column photo grid for Requirements & Fulfillment */
        .req-photos-grid-8 {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
            margin-top: 6px;
            max-width: 100%;
        }
        .req-photos-grid-8 img.req-photo-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .req-photos-grid-8 img.req-photo-thumb:hover {
            transform: scale(1.05);
            border-color: #0284c7;
        }

        /* Lightbox modal overlay */
        .req-lightbox-overlay {
            position: fixed;
            inset: 0;
            z-index: 1095;
            background: rgba(10, 15, 25, 0.95);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .req-lightbox-overlay[hidden] {
            display: none !important;
        }

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
            font-size: 12px;
            background: #e2e8f0;
            color: #334155;
            flex-shrink: 0;
        }
        .user-select-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .transition-transform {
            transition: transform 0.2s ease;
        }
        .collapsed .transition-transform {
            transform: rotate(-90deg);
        }

        /* Sticky Horizontal ScrollSpy Navigation Bar */
        .task-scrollspy-sticky {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            padding: 4px 0 6px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .task-scrollspy-nav {
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        .task-scrollspy-nav::-webkit-scrollbar {
            display: none;
        }
        .task-scrollspy-nav .spy-link {
            display: inline-flex;
            align-items: center;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 5px 11px;
            border-radius: 6px;
            color: #475569;
            text-decoration: none;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .task-scrollspy-nav .spy-link:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .task-scrollspy-nav .spy-link.active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .task-scrollspy-nav .spy-link.active .badge {
            background-color: rgba(255,255,255,0.2) !important;
            color: #ffffff !important;
        }

        .task-card-section {
            scroll-margin-top: 56px;
        }

        /* Compact Controls & Mobile Optimizations (<992px) */
        @media (max-width: 991.98px) {
            .edit-header-wrap {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.6rem;
            }
            .edit-header-actions {
                width: 100%;
            }
            .edit-header-actions .btn,
            .edit-header-actions form {
                flex: 1 1 0;
            }
            .edit-header-actions form .btn {
                width: 100%;
            }
            .form-control, .form-select {
                font-size: 0.8125rem !important;
                padding: 0.25rem 0.5rem !important;
                min-height: 32px !important;
            }
            .form-label {
                font-size: 0.68rem !important;
                margin-bottom: 0.15rem !important;
                font-family: 'IBM Plex Mono', monospace;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
            .btn-sm, .btn {
                font-size: 0.78rem !important;
                padding: 0.25rem 0.6rem !important;
            }
            .btn-touch {
                min-height: 32px !important;
                font-size: 0.78rem !important;
            }
            .btn-save-details {
                min-height: 34px !important;
                font-size: 0.8rem !important;
                padding: 0.3rem 0.85rem !important;
            }
            .btn-toggle-subtask {
                min-height: 28px !important;
                font-size: 0.72rem !important;
                padding: 0.2rem 0.6rem !important;
            }
            .input-group-subtask .btn {
                min-height: 32px !important;
                font-size: 0.78rem !important;
                padding: 0.25rem 0.6rem !important;
            }
            .input-group-subtask .form-control {
                min-height: 32px !important;
            }
            .user-select-row {
                padding: 6px 10px !important;
                min-height: 40px !important;
            }
            .user-avatar-circle {
                width: 28px !important;
                height: 28px !important;
                font-size: 11px !important;
            }
            .user-select-checkbox {
                width: 18px !important;
                height: 18px !important;
            }
            .btn-remove-assignee {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.75rem !important;
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

        .task-edit-wrapper {
            padding-bottom: 76px !important;
        }

        @media (max-width: 991.98px) {
            .mobile-bottom-nav {
                display: none !important;
            }
            .sticky-bottom-bar {
                left: 0 !important;
                padding: 10px 14px !important;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.15) !important;
            }
            .form-control, .form-select {
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
            .modal-footer .btn {
                min-height: 34px !important;
                font-size: 0.8rem !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="task-edit-wrapper">
    <!-- Live Toast Status Feedback Banner (hidden by default) -->
    <div id="ajax-toast-banner" class="toast-banner alert alert-success align-items-center gap-2 p-3 rounded" role="alert">
        <i id="ajax-toast-icon" class="bi bi-check-circle-fill fs-5"></i>
        <div id="ajax-toast-text" class="fw-semibold small"></div>
    </div>

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3 reveal edit-header-wrap">
        <div>
            <span class="eyebrow">Tasks · {{ $task->reference_number }}</span>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h1 class="h3 mb-0" id="task-header-title">{{ $task->title }}</h1>
                <span class="status-badge status-{{ $task->status === 'in_progress' ? 'warning' : ($task->status === 'completed' ? 'active' : 'muted') }}" id="task-status-badge">
                    {{ str_replace('_', ' ', $task->status) }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2 edit-header-actions">
            <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-touch d-inline-flex align-items-center justify-content-center">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Task Register
            </a>
            @if(auth()->user()->hasPermission('4.6'))
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-touch d-inline-flex align-items-center justify-content-center">
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

    <!-- Sticky Horizontal ScrollSpy Navigation Bar (Scrollable on overflow) -->
    <div class="task-scrollspy-sticky mb-3 reveal">
        <nav class="task-scrollspy-nav d-flex flex-nowrap align-items-center gap-1 p-1" id="taskScrollSpyNav" aria-label="Task section navigation">
            <a href="#card-details" class="spy-link active" data-target="card-details">
                <i class="bi bi-pencil-square me-1"></i>Details
            </a>
            <a href="#card-assignments" class="spy-link" data-target="card-assignments">
                <i class="bi bi-people me-1"></i>Assignments <span class="badge bg-secondary-subtle text-secondary ms-1" id="m-assign-count">{{ $task->assignments->count() }}</span>
            </a>
            @if($task->checklistSnapshot->isNotEmpty())
                <a href="#card-requirements" class="spy-link" data-target="card-requirements">
                    <i class="bi bi-clipboard-data me-1"></i>Requirements
                </a>
            @endif
            <a href="#card-subtasks" class="spy-link" data-target="card-subtasks">
                <i class="bi bi-check2-square me-1"></i>Subtasks <span class="badge bg-secondary-subtle text-secondary ms-1" id="m-subtasks-count">{{ $task->subtasks->count() }}</span>
            </a>
            <a href="#card-status-section" class="spy-link" data-target="card-status-section">
                <i class="bi bi-arrow-right-circle me-1"></i>Status
            </a>
            <a href="#card-history" class="spy-link" data-target="card-history">
                <i class="bi bi-clock-history me-1"></i>History <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $task->history->count() }}</span>
            </a>
            <a href="#card-snapshot" class="spy-link" data-target="card-snapshot">
                <i class="bi bi-info-circle me-1"></i>Info
            </a>
            @if(auth()->user()->hasPermission('4.4'))
                <a href="#card-evidence" class="spy-link" data-target="card-evidence">
                    <i class="bi bi-camera me-1"></i>Evidence
                </a>
            @endif
        </nav>
    </div>

    <div class="row g-3">
        <!-- Left Column: Details, Assignments (Second Card), Requirements, Subtasks -->
        <div class="col-lg-7">
            <!-- Card 1: Details & Schedule -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-details" style="--d: 80ms">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pencil-square me-1 text-accent"></i>Task Details & Schedule</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.update', $task) }}" id="form-task-details">
                        @csrf
                        @method('PUT')
                        <div class="row g-2">
                            <!-- 1. Property (First Input) -->
                            <div class="col-md-6">
                                <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                                <select name="property_id" id="property_id" class="form-select select2-searchable" required>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" @selected($task->property_id === $property->id)>{{ $property->dropdown_label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- 2. Title -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" value="{{ $task->title }}" class="form-control" required>
                            </div>
                            <!-- 3. Type -->
                            <div class="col-6 col-md-3">
                                <label for="task_type_id" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="task_type_id" id="task_type_id" class="form-select" required>
                                    @foreach ($taskTypes as $type)
                                        <option value="{{ $type->id }}" @selected($task->task_type_id === $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- 4. Priority -->
                            <div class="col-6 col-md-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select">
                                    @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                        <option value="{{ $priority }}" @selected($task->priority === $priority)>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $h = floor(($task->estimated_duration_minutes ?? 0) / 60);
                                $m = ($task->estimated_duration_minutes ?? 0) % 60;
                            @endphp
                            <!-- 5. Duration Hours -->
                            <div class="col-6 col-md-3">
                                <label for="duration_hours" class="form-label">Hours</label>
                                <input type="number" min="0" max="24" id="duration_hours" name="duration_hours" value="{{ $h }}" class="form-control">
                            </div>
                            <!-- 6. Duration Minutes -->
                            <div class="col-6 col-md-3">
                                <label for="duration_minutes" class="form-label">Minutes</label>
                                <input type="number" min="0" max="59" id="duration_minutes" name="duration_minutes" value="{{ $m }}" class="form-control">
                            </div>
                            <!-- 7. Scheduled Start -->
                            <div class="col-6 col-md-3">
                                <label for="scheduled_start_at" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" id="scheduled_start_at" name="scheduled_start_at"
                                       value="{{ $task->scheduled_start_at ? $task->scheduled_start_at->format('Y-m-d\TH:i') : '' }}"
                                       class="form-control">
                            </div>
                            <!-- 8. Scheduled End -->
                            <div class="col-6 col-md-3">
                                <label for="scheduled_end_at" class="form-label">End Date & Time</label>
                                <input type="datetime-local" id="scheduled_end_at" name="scheduled_end_at"
                                       value="{{ $task->scheduled_end_at ? $task->scheduled_end_at->format('Y-m-d\TH:i') : '' }}"
                                       class="form-control">
                            </div>
                            <!-- 9. Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">Description / Instructions</label>
                                <textarea id="description" name="description" rows="2" class="form-control">{{ $task->description }}</textarea>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <button type="submit" class="btn btn-primary btn-save-details px-3 py-1 fw-bold d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-check2 me-1"></i>Save Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card 2: Assignments (Pills with Cross & Compact Add) -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-assignments" style="--d: 100ms">
                <div class="card-header mono d-flex justify-content-between align-items-center py-2 px-3">
                    <span>
                        <i class="bi bi-people me-1 text-accent"></i>Assignments
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" id="assignments-count-badge">{{ $task->assignments->count() }}</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-0 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; height: 26px;" data-bs-toggle="modal" data-bs-target="#assignUserModal">
                        <i class="bi bi-plus-lg"></i><span>Add</span>
                    </button>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div id="assignments-list-container" class="d-flex flex-wrap align-items-center gap-2">
                        @forelse ($task->assignments as $assignment)
                            <div class="assignee-pill d-inline-flex align-items-center gap-2 rounded-pill assignment-row-item" data-assignment-id="{{ $assignment->id }}" data-user-id="{{ $assignment->assignee_id }}">
                                <div class="assignee-avatar-xs">{{ strtoupper(substr($assignment->assignee?->name ?? 'U', 0, 1)) }}</div>
                                <span class="fw-semibold extra-small text-dark text-truncate" style="max-width: 140px;">{{ $assignment->assignee?->name ?? ('#'.$assignment->assignee_id) }}</span>
                                <form method="POST" action="{{ route('tasks.unassign', [$task, $assignment]) }}" class="form-remove-assignment d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-remove-pill" aria-label="Remove {{ $assignment->assignee?->name }}">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-muted small mb-0" id="assignments-empty">No assignees yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Card 3: Requirements & Fulfillment (8 Images Per Row Grid & Lightbox) -->
            @if($task->checklistSnapshot->isNotEmpty())
                <div class="card shadow-sm mb-3 reveal task-card-section" id="card-requirements" style="--d: 120ms">
                    <div class="card-header mono"><i class="bi bi-clipboard-data me-1 text-accent"></i>Requirements & Fulfilment</div>
                    <div class="card-body">
                        @php
                            $doneCount = $task->checklistSnapshot->whereNotNull('completed_at')->count();
                            $totalCount = $task->checklistSnapshot->count();
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">{{ $doneCount }}/{{ $totalCount }} fulfilled</span>
                            <span class="status-badge status-{{ $doneCount === $totalCount ? 'active' : 'muted' }}">
                                <i class="bi bi-{{ $doneCount === $totalCount ? 'check2-circle' : 'hourglass-split' }} me-1"></i>
                                {{ $doneCount === $totalCount ? 'All complete' : 'In progress' }}
                            </span>
                        </div>
                        @foreach ($task->checklistSnapshot->groupBy('section_name') as $section => $items)
                            <div class="fw-semibold small text-uppercase text-muted mb-1">{{ $section }}</div>
                            <ul class="list-unstyled ms-2 mb-3">
                                @foreach ($items as $item)
                                    <li class="small mb-2 p-2 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <i class="bi bi-{{ $item->completed_at ? 'check2-circle text-success' : 'circle text-muted' }} me-1" aria-hidden="true"></i>
                                                <span class="fw-semibold text-dark">{{ $item->item_label }}</span>
                                                @if($item->is_photo_required)<span class="text-danger extra-small">*photo</span>@endif
                                                @if($item->is_comment_required)<span class="text-danger extra-small">*comment</span>@endif
                                            </div>
                                            <span class="status-badge status-{{ $item->completed_at ? 'active' : 'muted' }} flex-shrink-0">{{ $item->completed_at ? 'done' : 'pending' }}</span>
                                        </div>
                                        @if(!empty($item->comment))
                                            <div class="text-secondary ms-3 mt-1 small bg-light p-1 rounded">
                                                <i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>{{ $item->comment }}
                                            </div>
                                        @endif
                                        @php $editPhotos = is_array($item->photo_url) ? $item->photo_url : (!empty($item->photo_url) ? [$item->photo_url] : []); @endphp
                                        @if(count($editPhotos))
                                            <div class="req-photos-grid-8 ms-3 mt-2">
                                                @foreach($editPhotos as $photoUrl)
                                                    <img src="{{ $photoUrl }}" alt="Requirement photo" class="req-photo-thumb"
                                                         data-photo-src="{{ $photoUrl }}">
                                                @endforeach
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Card 4: Subtasks (Axios Async Toggle & Add) -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-subtasks" style="--d: 140ms">
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
                                    <button type="button" class="btn btn-sm btn-outline-{{ $subtask->completed_at ? 'secondary' : 'success' }} btn-toggle-subtask rounded-pill fw-semibold"
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
                        <div class="input-group input-group-subtask">
                            <input type="text" name="title" id="new-subtask-title" class="form-control" placeholder="Add a sub task…" required>
                            <button type="submit" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-3">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Subtask
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Status Transition, Status History, Snapshot & Evidence -->
        <div class="col-lg-5">
            <!-- Card 5: Status Transition -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-status-section" style="--d: 100ms">
                <div class="card-header mono d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-right-circle me-1 text-accent"></i>Status</span>
                    @include('partials.task-status-icon', ['task' => $task])
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tasks.transition', $task) }}" id="form-move-status" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label for="status" class="form-label small">Select New Status</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                @foreach (['not_started' => 'Not Started', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $stKey => $stLabel)
                                    <option value="{{ $stKey }}" @selected($task->simplified_status === $stKey)>{{ $stLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label small">Transition Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100 fw-bold py-2 d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-arrow-right-circle me-1" aria-hidden="true"></i>Move Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card 6: Status History Table -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-history" style="--d: 160ms">
                <div class="card-header mono d-flex justify-content-between align-items-center collapsed" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseStatusHistory" aria-expanded="false" aria-controls="collapseStatusHistory">
                    <span>
                        <i class="bi bi-clock-history me-1 text-accent"></i>Status History
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $task->history->count() }}</span>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        @if($task->worked_seconds)
                            <span class="status-badge status-active"><i class="bi bi-stopwatch me-1"></i>Worked {{ gmdate('H:i:s', (int) $task->worked_seconds) }}</span>
                        @endif
                        <i class="bi bi-chevron-down text-muted transition-transform"></i>
                    </div>
                </div>
                <div class="collapse" id="collapseStatusHistory">
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

            <!-- Card 7: Task Snapshot Info -->
            <div class="card shadow-sm mb-3 reveal task-card-section" id="card-snapshot" style="--d: 180ms">
                <div class="card-header mono"><i class="bi bi-info-circle me-1 text-accent"></i>Snapshot & Location</div>
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
                <div class="task-card-section" id="card-evidence">
                    @include('partials.evidence-upload', ['task' => $task])
                </div>
            @endif
        </div>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="sticky-bottom-bar d-flex align-items-center justify-content-end gap-2">
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm px-3 flex-fill flex-md-grow-0">Cancel</a>
        <button type="submit" form="form-task-details" class="btn btn-primary btn-sm fw-bold px-4 flex-fill flex-md-grow-0 btn-save-details">
            <i class="bi bi-check2 me-1"></i>Save Changes
        </button>
    </div>
</div>

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
                        $assignedIds = $task->assignments->where('assignee_type', 'user')->pluck('assignee_id')->toArray();
                        $userList = $people ?? $cleaners ?? [];
                    @endphp
                    <div class="user-select-list d-flex flex-column gap-1" id="assignee-user-list">
                        @forelse($userList as $u)
                            @php $isAssigned = in_array($u->id, $assignedIds); @endphp
                            <label class="user-select-row d-flex align-items-center justify-content-between p-2 rounded {{ $isAssigned ? 'd-none' : '' }}" data-user-name="{{ strtolower($u->name) }}" data-user-role="{{ strtolower($u->role ?? '') }}" data-user-id="{{ $u->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input user-select-checkbox m-0" value="{{ $u->id }}" data-user-name="{{ $u->name }}">
                                    <div class="user-avatar-circle">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold small text-dark mb-0">{{ $u->name }}</div>
                                        <span class="extra-small text-muted text-uppercase mono">{{ $u->role ?? 'user' }}</span>
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
                            <i class="bi bi-check2 me-1"></i>Save Assignments
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirement Photo Lightbox Modal (Prev/Next, Swipe, Close & Counter) -->
    <div class="req-lightbox-overlay" id="reqLightboxModal" hidden>
        <!-- Top bar -->
        <div class="d-flex justify-content-between align-items-center w-100 p-2 p-sm-3" style="z-index: 30;">
            <span class="badge bg-dark bg-opacity-75 text-white border border-secondary px-3 py-1 mono fs-6" id="lightbox-counter">1 / 1</span>
            <div class="d-flex align-items-center gap-2">
                <a href="#" id="lightbox-open-full" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light rounded-pill px-3 extra-small">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Full size
                </a>
                <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center" id="lightbox-close-btn" style="width: 36px; height: 36px;" aria-label="Close">
                    <i class="bi bi-x-lg fs-6"></i>
                </button>
            </div>
        </div>

        <!-- Center Image with Prev/Next buttons + Swipe Touch Container -->
        <div class="d-flex align-items-center justify-content-center flex-grow-1 position-relative overflow-hidden my-2" id="lightbox-touch-area" style="touch-action: pan-y;">
            <button type="button" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute start-0 ms-2 ms-sm-4 p-0 d-flex align-items-center justify-content-center shadow-lg border border-secondary" id="lightbox-prev-btn" style="width: 46px; height: 46px; z-index: 25;" aria-label="Previous photo">
                <i class="bi bi-chevron-left fs-4"></i>
            </button>
            <img src="" alt="Requirement Photo" id="lightbox-preview-img" class="img-fluid rounded shadow-lg" style="max-height: 80vh; max-width: 90vw; object-fit: contain; transition: opacity 0.15s ease;">
            <button type="button" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute end-0 me-2 me-sm-4 p-0 d-flex align-items-center justify-content-center shadow-lg border border-secondary" id="lightbox-next-btn" style="width: 46px; height: 46px; z-index: 25;" aria-label="Next photo">
                <i class="bi bi-chevron-right fs-4"></i>
            </button>
        </div>

        <!-- Bottom bar spacer -->
        <div class="p-2 text-center text-white-50 extra-small mono" style="z-index: 30;">
            Use arrows or swipe to navigate photos · ESC to close
        </div>
    </div>
@endsection

@push('scripts')
    <script>@include('partials.evidence-upload-js', ['task' => $task])</script>
    <script>
        (function ($) {
            if ($.fn.select2) {
                $('#property_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search or pick a property…',
                    allowClear: true,
                    width: '100%'
                });
            }

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
                $toast.addClass('show-toast').fadeIn(200);

                if (toastTimer) clearTimeout(toastTimer);
                if (type !== 'loading') {
                    toastTimer = setTimeout(function () {
                        $toast.fadeOut(300, function () {
                            $(this).removeClass('show-toast');
                        });
                    }, 2500);
                }
            }

            // ScrollSpy Navigation with auto horizontal scroll centering
            var isUserClickScrolling = false;
            var clickScrollTimer = null;

            function setActiveSpyLink(targetId) {
                var $activeLink = $('#taskScrollSpyNav .spy-link[data-target="' + targetId + '"]');
                if (!$activeLink.length || $activeLink.hasClass('active')) return;

                $('#taskScrollSpyNav .spy-link').removeClass('active');
                $activeLink.addClass('active');

                // Auto scroll horizontal track to keep active pill visible & centered
                var navContainer = document.getElementById('taskScrollSpyNav');
                var activeElement = $activeLink[0];
                if (navContainer && activeElement) {
                    var navRect = navContainer.getBoundingClientRect();
                    var elemRect = activeElement.getBoundingClientRect();
                    var scrollLeftTarget = navContainer.scrollLeft + (elemRect.left - navRect.left) - (navRect.width / 2) + (elemRect.width / 2);
                    navContainer.scrollTo({
                        left: Math.max(0, scrollLeftTarget),
                        behavior: 'smooth'
                    });
                }
            }

            // Click spy pill to smooth scroll to section
            $('#taskScrollSpyNav .spy-link').on('click', function (e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                var targetElement = document.getElementById(targetId);
                if (!targetElement) return;

                isUserClickScrolling = true;
                if (clickScrollTimer) clearTimeout(clickScrollTimer);

                $('#taskScrollSpyNav .spy-link').removeClass('active');
                $(this).addClass('active');

                var navContainer = document.getElementById('taskScrollSpyNav');
                var activeElement = this;
                if (navContainer && activeElement) {
                    var navRect = navContainer.getBoundingClientRect();
                    var elemRect = activeElement.getBoundingClientRect();
                    var scrollLeftTarget = navContainer.scrollLeft + (elemRect.left - navRect.left) - (navRect.width / 2) + (elemRect.width / 2);
                    navContainer.scrollTo({
                        left: Math.max(0, scrollLeftTarget),
                        behavior: 'smooth'
                    });
                }

                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

                clickScrollTimer = setTimeout(function () {
                    isUserClickScrolling = false;
                }, 700);
            });

            // Scroll position detector for active section
            $(window).on('scroll', function () {
                if (isUserClickScrolling) return;
                var scrollPos = $(window).scrollTop() + 110;
                var currentCardId = null;

                $('.task-card-section').each(function () {
                    var top = $(this).offset().top;
                    var height = $(this).outerHeight();
                    if (scrollPos >= top && scrollPos < top + height) {
                        currentCardId = $(this).attr('id');
                    }
                });

                if (!currentCardId && $(window).scrollTop() < 120) {
                    currentCardId = 'card-details';
                }

                if (currentCardId) {
                    setActiveSpyLink(currentCardId);
                }
            });

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
                        var msg = err.response?.data?.message || 'Error saving task details.';
                        if (err.response?.data?.errors) {
                            var firstErr = Object.values(err.response.data.errors)[0];
                            if (Array.isArray(firstErr) && firstErr.length) {
                                msg = firstErr[0];
                            }
                        }
                        showToast(msg, 'error');
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
                            '<button type="button" class="btn btn-sm btn-outline-success btn-toggle-subtask rounded-pill fw-semibold" data-url="' + toggleUrl + '">Done</button>' +
                            '</div>';
                        $('#subtasks-list-container').append(html);

                        var currentCount = parseInt($('#subtasks-count').text()) || 0;
                        var newCount = currentCount + 1;
                        $('#subtasks-count').text(newCount);
                        $('#m-subtasks-count').text(newCount);
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
                            $btn.text('Reopen').removeClass('btn-outline-success').addClass('btn-outline-secondary');
                            showToast('Subtask completed!', 'success');
                        } else {
                            $title.removeClass('completed-subtask');
                            $icon.attr('class', 'bi bi-circle text-muted me-1 subtask-icon');
                            $btn.text('Done').removeClass('btn-outline-secondary').addClass('btn-outline-success');
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

            // 5. User Search in Assign Modal
            function getCurrentlyAssignedIds() {
                var ids = [];
                $('.assignment-row-item').each(function() {
                    var uid = $(this).data('user-id');
                    if (uid) ids.push(parseInt(uid));
                });
                return ids;
            }

            $('#assignUserModal').on('show.bs.modal', function () {
                $('#assignee-search-input').val('');
                $('#clear-search-btn').addClass('d-none');
                $('#assignee-user-list .user-select-checkbox').prop('checked', false);

                var assignedIds = getCurrentlyAssignedIds();
                $('#assignee-user-list .user-select-row').each(function() {
                    var uid = parseInt($(this).data('user-id'));
                    if (assignedIds.includes(uid)) {
                        $(this).addClass('d-none');
                    } else {
                        $(this).removeClass('d-none');
                    }
                });
                updateSelectedAssigneesCount();
            });

            $('#assignee-search-input').on('input', function() {
                var query = $(this).val().toLowerCase().trim();
                $('#clear-search-btn').toggleClass('d-none', query === '');
                var assignedIds = getCurrentlyAssignedIds();

                $('#assignee-user-list .user-select-row').each(function() {
                    var uid = parseInt($(this).data('user-id'));
                    if (assignedIds.includes(uid)) {
                        $(this).addClass('d-none');
                        return;
                    }
                    var name = $(this).data('user-name') || '';
                    var role = $(this).data('user-role') || '';
                    if (name.includes(query) || role.includes(query)) {
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
            }

            $(document).on('change', '.user-select-checkbox', function() {
                updateSelectedAssigneesCount();
            });
            updateSelectedAssigneesCount();

            // 6. Save Assignments via Axios
            $('#btn-save-assignments').on('click', function() {
                const modalEl = document.getElementById('assignUserModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                const newlyCheckedIds = [];
                const alreadyAssignedIds = getCurrentlyAssignedIds();

                $('#assignee-user-list .user-select-checkbox:checked').each(function() {
                    const uid = parseInt($(this).val());
                    if (!alreadyAssignedIds.includes(uid)) {
                        newlyCheckedIds.push(uid);
                    }
                });

                if (!newlyCheckedIds.length) {
                    if (modalInstance) modalInstance.hide();
                    showToast('Assignments updated.', 'success');
                    return;
                }

                showToast('Assigning ' + newlyCheckedIds.length + ' user(s)…', 'loading');
                const assignUrl = '{{ route('tasks.assign', $task) }}';
                
                const requests = newlyCheckedIds.map(function(userId) {
                    return axios.post(assignUrl, {
                        assignee_type: 'user',
                        assignee_id: userId
                    });
                });

                Promise.all(requests)
                    .then(function(responses) {
                        $('#assignments-empty').hide();
                        responses.forEach(function(res) {
                            const a = res.data.assignment;
                            const initial = (a.assignee_name || 'U').charAt(0).toUpperCase();
                            const pillHtml = '<div class="assignee-pill d-inline-flex align-items-center gap-2 rounded-pill assignment-row-item" data-assignment-id="' + a.id + '" data-user-id="' + a.assignee_id + '">' +
                                '<div class="assignee-avatar-xs">' + initial + '</div>' +
                                '<span class="fw-semibold extra-small text-dark text-truncate" style="max-width: 140px;">' + $('<div>').text(a.assignee_name).html() + '</span>' +
                                '<form method="POST" action="' + a.delete_url + '" class="form-remove-assignment d-inline m-0">' +
                                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                                '<input type="hidden" name="_method" value="DELETE">' +
                                '<button type="submit" class="btn-remove-pill" aria-label="Remove ' + $('<div>').text(a.assignee_name).html() + '">' +
                                '<i class="bi bi-x"></i>' +
                                '</button>' +
                                '</form>' +
                                '</div>';
                            $('#assignments-list-container').append(pillHtml);

                            // Hide from modal list
                            $('#assignee-user-list .user-select-row[data-user-id="' + a.assignee_id + '"]').addClass('d-none');
                        });

                        var totalAssignments = $('.assignment-row-item').length;
                        $('#m-assign-count').text(totalAssignments);
                        $('#assignments-count-badge').text(totalAssignments);

                        if (modalInstance) modalInstance.hide();
                        showToast('Assignments saved successfully!', 'success');
                    })
                    .catch(function(err) {
                        showToast(err.response?.data?.message || 'Error saving assignments.', 'error');
                    });
            });

            // 7. Remove Assignee via Axios
            $(document).on('submit', '.form-remove-assignment', function (e) {
                e.preventDefault();
                if (!confirm('Remove assignment?')) return;

                var $form = $(this);
                var $row = $form.closest('.assignment-row-item');
                var userId = $row.data('user-id');
                showToast('Removing assignment…', 'loading');

                axios.delete($form.attr('action'))
                    .then(function (res) {
                        showToast('Assignment removed.', 'success');
                        $row.fadeOut(200, function () {
                            $(this).remove();
                            var totalAssignments = $('.assignment-row-item').length;
                            $('#m-assign-count').text(totalAssignments);
                            $('#assignments-count-badge').text(totalAssignments);
                            if (!totalAssignments) {
                                $('#assignments-empty').show();
                            }
                        });
                        if (userId) {
                            $('#assignee-user-list .user-select-checkbox[value="' + userId + '"]').prop('checked', false);
                            $('#assignee-user-list .user-select-row[data-user-id="' + userId + '"]').removeClass('d-none');
                            updateSelectedAssigneesCount();
                        }
                    })
                    .catch(function (err) {
                        showToast(err.response?.data?.message || 'Error removing assignment.', 'error');
                    });
            });

            // 8. Requirement Photos Lightbox (Multi-photo Navigation, Counter, Swipe & Keys)
            let lightboxPhotos = [];
            let currentLightboxIndex = 0;

            function updateLightboxPhoto() {
                if (!lightboxPhotos.length) return;
                const photoUrl = lightboxPhotos[currentLightboxIndex];
                $('#lightbox-preview-img').css('opacity', 0.2);
                setTimeout(() => {
                    $('#lightbox-preview-img').attr('src', photoUrl).css('opacity', 1);
                }, 80);
                $('#lightbox-open-full').attr('href', photoUrl);
                $('#lightbox-counter').text((currentLightboxIndex + 1) + ' / ' + lightboxPhotos.length);
                $('#lightbox-prev-btn').toggle(lightboxPhotos.length > 1);
                $('#lightbox-next-btn').toggle(lightboxPhotos.length > 1);
            }

            function openLightbox(photos, startIndex = 0) {
                if (!photos || !photos.length) return;
                lightboxPhotos = photos;
                currentLightboxIndex = Math.max(0, Math.min(startIndex, photos.length - 1));
                updateLightboxPhoto();
                $('#reqLightboxModal').removeAttr('hidden').fadeIn(150);
                $('body').addClass('modal-open');
            }

            function closeLightbox() {
                $('#reqLightboxModal').fadeOut(120, function() {
                    $(this).attr('hidden', true);
                });
                $('body').removeClass('modal-open');
            }

            $(document).on('click', '.req-photo-thumb', function () {
                const $container = $(this).closest('.req-photos-grid-8');
                const allPhotos = [];
                let initialIndex = 0;
                const clickedSrc = $(this).data('photo-src') || $(this).attr('src');

                $container.find('.req-photo-thumb').each(function (idx) {
                    const src = $(this).data('photo-src') || $(this).attr('src');
                    if (src) {
                        allPhotos.push(src);
                        if (src === clickedSrc) {
                            initialIndex = idx;
                        }
                    }
                });

                if (allPhotos.length) {
                    openLightbox(allPhotos, initialIndex);
                }
            });

            $('#lightbox-prev-btn').on('click', function(e) {
                e.stopPropagation();
                if (lightboxPhotos.length > 1) {
                    currentLightboxIndex = (currentLightboxIndex - 1 + lightboxPhotos.length) % lightboxPhotos.length;
                    updateLightboxPhoto();
                }
            });

            $('#lightbox-next-btn').on('click', function(e) {
                e.stopPropagation();
                if (lightboxPhotos.length > 1) {
                    currentLightboxIndex = (currentLightboxIndex + 1) % lightboxPhotos.length;
                    updateLightboxPhoto();
                }
            });

            $('#lightbox-close-btn').on('click', function() {
                closeLightbox();
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if (!$('#reqLightboxModal').is(':hidden')) {
                    if (e.key === 'ArrowLeft') {
                        $('#lightbox-prev-btn').click();
                    } else if (e.key === 'ArrowRight') {
                        $('#lightbox-next-btn').click();
                    } else if (e.key === 'Escape') {
                        closeLightbox();
                    }
                }
            });

            // Touch Swipe navigation
            let touchStartX = 0;
            let touchStartY = 0;
            const touchArea = document.getElementById('lightbox-touch-area');
            if (touchArea) {
                touchArea.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartY = e.changedTouches[0].screenY;
                }, { passive: true });

                touchArea.addEventListener('touchend', function(e) {
                    const touchEndX = e.changedTouches[0].screenX;
                    const touchEndY = e.changedTouches[0].screenY;
                    const diffX = touchEndX - touchStartX;
                    const diffY = touchEndY - touchStartY;

                    if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
                        if (diffX < 0) {
                            $('#lightbox-next-btn').click();
                        } else {
                            $('#lightbox-prev-btn').click();
                        }
                    }
                }, { passive: true });
            }
        })(jQuery);
    </script>
@endpush
