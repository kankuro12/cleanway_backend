@extends('layouts.app')

@section('title', $task->title)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .forest-hero-banner {
            background-color: #064e3b;
            color: #ffffff;
            padding: 1.5rem 1.25rem 3rem 1.25rem;
            position: relative;
            z-index: 0;
        }
        .forest-hero-title {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }
        .forest-hero-subtitle {
            font-size: 0.95rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        .guest-slanted-chip {
            background-color: #e2e8f0;
            color: #334155;
            font-family: var(--cw-font-mono, monospace);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35rem 1.25rem;
            clip-path: polygon(0 0, 92% 0, 100% 100%, 0% 100%);
            display: inline-block;
        }
        .task-work-card-container {
            margin-top: -1.75rem;
            background: #ffffff;
            border-radius: 16px 16px 0 0;
            padding: 1.5rem 1.25rem;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
            position: relative;
            z-index: 1;
        }
        .counter-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 0.5rem;
            text-align: center;
            background: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
        }
        .counter-card:hover {
            border-color: #0284c7;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.12);
        }
        .counter-card-icon {
            font-size: 1.25rem;
            color: #475569;
            margin-bottom: 0.25rem;
        }
        .counter-card-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }
        .req-category-tab-bar {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border-bottom: 2px solid #e2e8f0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
            margin-bottom: 1.25rem;
        }
        .req-category-tab-bar::-webkit-scrollbar { display: none; }
        .req-tab-item {
            font-size: 0.9rem;
            font-weight: 700;
            color: #64748b;
            padding: 0.6rem 0.25rem;
            text-decoration: none;
            position: relative;
            cursor: pointer;
        }
        .req-tab-item.active {
            color: #0284c7;
        }
        .req-tab-item.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #0284c7;
            border-radius: 3px 3px 0 0;
        }
        .req-item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            margin-bottom: 0.75rem;
        }
        .req-item-text {
            font-size: 0.85rem;
            line-height: 1.45;
            color: #1e293b;
            margin-bottom: 0.6rem;
        }
        .checklist-action-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 999px;
            padding: 0.2rem 0.85rem;
            font-weight: 600;
            font-size: 0.75rem;
            color: #475569;
            transition: all 0.15s ease;
        }
        .checklist-action-btn.active {
            background-color: #dcfce7;
            border-color: #16a34a;
            color: #15803d;
        }
        .comment-bubble {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
        }
        .req-photo-preview img.req-photo-thumb {
            width: 84px;
            height: 84px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: zoom-in;
            display: inline-block;
        }
        .req-photo-preview img.req-photo-thumb:hover {
            border-color: #0284c7;
        }
        .req-card-photo {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
            margin-bottom: 0.6rem;
        }
        .req-card-photo img.req-photo-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            height: auto;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            cursor: zoom-in;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .req-card-photo img.req-photo-thumb:hover {
            border-color: #0284c7;
            transform: scale(1.05);
        }
        .req-icon-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 50%;
            background: #ffffff;
            color: #64748b;
            font-size: 0.95rem;
            cursor: pointer;
            transition: border-color 0.15s ease, color 0.15s ease;
        }
        .req-icon-btn:hover {
            border-color: #0284c7;
            color: #0284c7;
        }
        .req-photo-manager-item {
            position: relative;
            width: 96px;
            height: 96px;
        }
        .req-photo-manager-item img {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            cursor: zoom-in;
        }
        .req-photo-manager-item .btn-del-photo {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #fee2e2;
            background: #ffffff;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        #photoPreviewModal img {
            max-height: 80vh;
            width: 100%;
            object-fit: contain;
        }
        .req-cat-sticky {
            position: sticky;
            top: 0;
            z-index: 10;
            margin-left: -1rem;
            margin-right: -1rem;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        .req-header-title {
            min-width: 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            flex: 1 1 auto;
        }
        .req-header-timer {
            flex: 0 0 auto;
        }
        .req-header-actions {
            flex: 0 0 auto;
        }
        #punch-map { height: 240px; border-radius: 6px; }

        /* Stacked overlay for child popups (comment / photo) — sits above parent modal */
        .req-stack-overlay {
            position: fixed;
            inset: 0;
            z-index: 1070;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.45);
        }
        .req-stack-overlay[hidden] { display: none; }
        .req-stack-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 80vh;
        }
        .req-stack-card-lg { max-width: 560px; }
        .req-stack-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .req-stack-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .req-stack-body { padding: 1rem; overflow-y: auto; }
        .req-stack-foot {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        @media (max-width: 767.98px) {
            .modal.fade .modal-dialog {
                margin: 0 !important;
                max-width: 100% !important;
                width: 100vw !important;
                min-height: 100vh !important;
                height: 100vh !important;
            }
            .modal.fade .modal-content {
                height: 100% !important;
                border-radius: 0 !important;
                border: none !important;
                display: flex !important;
                flex-direction: column !important;
            }
            .modal-header {
                padding: 0.5rem 0.75rem !important;
                background: #ffffff !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            .req-header {
                padding: 0.5rem 0.5rem !important;
                gap: 0.35rem !important;
            }
            .req-header .btn-close {
                margin: 0 0.25rem 0 0 !important;
                flex: 0 0 auto;
            }
            .req-header-title {
                font-size: 0.8rem !important;
                flex-shrink: 1;
            }
            .req-header-timer {
                font-size: 0.8rem !important;
            }
            .req-header-btn {
                padding: 0.25rem 0.45rem !important;
                font-size: 0.7rem !important;
                line-height: 1.2 !important;
            }
            .req-header-btn .bi {
                margin: 0 !important;
            }
            .modal-body {
                padding: 1rem !important;
                flex: 1 1 auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            .admin-content {
                padding: 0 !important;
            }
            .forest-hero-banner {
                padding: 1rem 0.75rem 1.25rem 0.75rem;
            }
            .task-work-card-container {
                padding: 1rem 0.6rem;
                margin-top: 0;
                border-radius: 0 0 0 0;
                box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
            }
            .counter-card {
                padding: 0.6rem 0.3rem;
            }
            .counter-card-icon {
                font-size: 1rem;
            }
            .counter-card-label {
                font-size: 0.68rem;
            }
            .req-item-card {
                padding: 0.9rem;
            }
            .req-card-photo {
                grid-template-columns: repeat(8, 1fr);
                gap: 3px;
            }
            .req-card-photo img.req-photo-thumb {
                aspect-ratio: 1 / 1;
                height: auto;
            }
            .req-item-card .btn {
                font-size: 0.72rem;
                padding: 0.3rem 0.9rem;
            }
            #btn-checkin,
            #btn-running-timer,
            #btn-complete {
                font-size: 0.9rem;
                padding-top: 0.6rem !important;
                padding-bottom: 0.6rem !important;
            }
            #btn-running-timer #task-shift-timer {
                font-size: 1.1rem;
            }
        }
    </style>
@endpush

@section('content')
@php $canEdit = $canEdit ?? (in_array($task->status, ['in_progress', 'paused'], true)); @endphp
<div class="container-fluid p-0">
    <!-- Forest Green Hero Header -->
    <div class="forest-hero-banner">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ auth()->user()->hasPermission('4.9') ? route('tasks') : route('tasks.my') }}" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px; height:36px;" title="Back to tasks">
                <i class="bi bi-arrow-left"></i>
            </a>
            <button type="button" class="btn btn-outline-light btn-sm rounded-circle p-2" style="width:36px; height:36px;" title="Share / Options">
                <i class="bi bi-box-arrow-up-right"></i>
            </button>
        </div>

        <!-- Task Title & Priority Badge in Upper Hero Section -->
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <h1 class="h2 fw-bold text-white mb-0">{{ $task->taskType?->name ?? $task->title }}</h1>
            <div class="d-flex flex-column align-items-end gap-1">
                <span class="badge text-white rounded-pill px-3 py-2 mono text-xs uppercase" style="background-color: #10b981;">
                    <i class="bi bi-diamond-fill me-1"></i>{{ strtoupper($task->priority ?? 'medium') }}
                </span>
                <div class="text-white-50 small fw-bold"><i class="bi bi-calendar-event me-1"></i>{{ $task->scheduled_start_at?->format('D, M j H:i') ?? 'Today' }}</div>
            </div>
        </div>

        <div class="forest-hero-title fs-6 mb-1 opacity-90">
            <i class="bi bi-building me-1"></i>{{ $task->property_name_snapshot ?? $task->property?->name ?? 'Property' }}
            @if($task->property?->property_code)({{ $task->property->property_code }})@elseif($task->reference_number)({{ $task->reference_number }})@endif
            — {{ floor(($task->estimated_duration_minutes ?? 80) / 60) > 0 ? floor(($task->estimated_duration_minutes ?? 80) / 60).'H ' : '' }}{{ ($task->estimated_duration_minutes ?? 80) % 60 }}M
        </div>
        <div class="forest-hero-subtitle opacity-75">
            <i class="bi bi-geo-alt me-1"></i>{{ $task->property?->address ?? 'Location address' }}
        </div>
    </div>

    <!-- Main Card Body Container -->
    <div class="task-work-card-container">

        <!-- Due On & Description -->
        <div class="mb-3">
            <div class="text-muted small fw-bold mb-1">Description</div>
            <p class="text-secondary small mb-0">{{ $task->description ?? 'Turnover housekeeping to be completed at checkout.' }}</p>
        </div>

        <!-- 3 Counter Cards matching Screenshot 1 -->
        @php
            $totalReqs = $task->checklistSnapshot->count();
            $doneReqs = $task->checklistSnapshot->whereNotNull('completed_at')->count();
            $commentCount = $task->comments->count() + $task->history->whereNotNull('remarks')->count();
        @endphp
        <div class="row g-2 mb-4">
            <div class="col-4">
                <div class="counter-card" id="btn-open-requirements">
                    <div class="counter-card-icon"><i class="bi bi-list-task text-primary"></i> <span id="req-counter-val">{{ $doneReqs }}/{{ $totalReqs }}</span></div>
                    <div class="counter-card-label">Requirements</div>
                </div>
            </div>
            <div class="col-4">
                <div class="counter-card" id="btn-open-attachments">
                    <div class="counter-card-icon"><i class="bi bi-paperclip text-secondary"></i> {{ $task->evidence->count() }}</div>
                    <div class="counter-card-label">Attachments</div>
                </div>
            </div>
            <div class="col-4">
                <div class="counter-card" id="btn-open-comments">
                    <div class="counter-card-icon"><i class="bi bi-chat-dots text-info"></i> <span id="comments-counter-val">{{ $commentCount }}</span></div>
                    <div class="counter-card-label">Comments</div>
                </div>
            </div>
        </div>

        <!-- Punch In & Shift Timer Control -->
        <div id="work-alert" class="alert py-2 mb-3" role="alert" style="display: none;"></div>

        <div class="card border-0 bg-light p-3 mb-3">
            @if(in_array($task->status, ['assigned', 'accepted']))
                <button type="button" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm rounded-pill" id="btn-checkin">
                    <i class="bi bi-play-fill me-1"></i>Punch in & start
                </button>
                @if(config('gps.geofence_enforced', false))
                    <div class="text-muted extra-small text-center mt-2" id="gps-status">
                        <i class="bi bi-geo-alt me-1"></i>Location verified against property geofence.
                    </div>
                @endif
            @else
                <!-- Dynamic Live Shift Timer Button -->
                <button type="button" class="btn {{ $task->status === 'paused' ? 'btn-outline-success text-success-emphasis' : 'btn-outline-info text-info-emphasis' }} btn-lg w-100 fw-bold py-3 rounded-pill border-2 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-running-timer" title="{{ $task->status === 'paused' ? 'Resume task' : 'Pause task' }}">
                    <i class="bi {{ $task->status === 'paused' ? 'bi-play-fill text-success' : 'bi-pause-fill text-info' }} fs-4"></i>
                    <span class="mono fs-3" id="task-shift-timer">00:00:00</span>
                </button>
            @endif
        </div>

        @if(config('gps.geofence_enforced', false))
            <!-- Task Punch Record Card -->
            <div class="card shadow-sm mb-3 {{ $lastPunch ? '' : 'd-none' }}" id="punch-card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="mono text-xs text-uppercase">Punch-in Location Record</span>
                    <span class="badge bg-success mono" id="punch-badge">Inside Geofence</span>
                </div>
                <div class="card-body">
                    <div id="punch-map" class="mb-2"></div>
                    <div class="mono text-xs text-muted" id="punch-reason"></div>
                </div>
            </div>
        @endif

        <!-- Finish Task Card -->
        @if($canEdit)
        <div class="card shadow-sm mb-3" id="finish-card">
            <div class="card-header bg-dark text-white mono py-2">Finish & Submit</div>
            <div class="card-body">
                <label for="work-remarks" class="form-label small fw-bold">Completion Remarks</label>
                <textarea id="work-remarks" rows="2" class="form-control form-control-sm mb-2" placeholder="Notes, keys returned, items cleaned..."></textarea>
                <button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-pill" id="btn-complete">
                    <i class="bi bi-check2-circle me-1"></i>Complete Task
                </button>
                <div class="text-muted extra-small mt-2" id="complete-status"></div>
            </div>
        </div>
        @else
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white mono py-2">Task Status</div>
            <div class="card-body d-flex align-items-center justify-content-between">
                <span class="status-badge status-{{ in_array($task->status, ['completed', 'approved'], true) ? 'active' : 'muted' }}">
                    <i class="bi bi-{{ in_array($task->status, ['completed', 'approved'], true) ? 'check2-circle' : 'hourglass-split' }} me-1"></i>
                    {{ str_replace('_', ' ', $task->status) }}
                </span>
                @if(in_array($task->status, ['completed', 'submitted_for_approval', 'approved'], true))
                    <span class="extra-small text-muted">Read only — {{ $task->status === 'approved' ? 'approved' : 'submitted for approval' }}</span>
                @else
                    <span class="extra-small text-muted"><i class="bi bi-lock me-1"></i>Read only before work starts</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Categorized Requirements Modal -->
<div class="modal fade" id="requirementsModal" tabindex="-1" aria-labelledby="requirementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-lg">
        <div class="modal-content">
            <!-- Top Header: title + live timer + pause/complete actions -->
            <div class="modal-header bg-white border-bottom py-2 gap-1 gap-sm-2 align-items-center req-header">
                <button type="button" class="btn-close ms-0 me-1 me-sm-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title h6 fw-bold text-dark mb-0 me-auto req-header-title text-truncate" id="requirementsModalLabel">Requirements</h5>
                <span class="mono fs-6 fs-sm-5 fw-bold text-muted modal-timer req-header-timer">00:00:00</span>
                @if($canEdit)
                <div class="d-flex gap-1 gap-sm-2 req-header-actions">
                    <button type="button" class="btn {{ $task->status === 'paused' ? 'btn-outline-success' : 'btn-outline-info' }} fw-bold btn-sm rounded-pill req-header-btn" id="btn-pause" aria-label="{{ $task->status === 'paused' ? 'Resume task' : 'Pause task' }}" title="{{ $task->status === 'paused' ? 'Resume task' : 'Pause task' }}">
                        <i class="bi {{ $task->status === 'paused' ? 'bi-play-fill' : 'bi-pause-fill' }} me-0 me-sm-1"></i><span class="req-btn-txt d-none d-sm-inline">{{ $task->status === 'paused' ? 'Resume' : 'Pause' }}</span>
                    </button>
                    <button type="button" class="btn btn-success fw-bold btn-sm rounded-pill req-header-btn" id="btn-complete-modal" aria-label="Complete task">
                        <i class="bi bi-check2-circle me-0 me-sm-1"></i><span class="req-btn-txt d-none d-sm-inline">Complete</span>
                    </button>
                </div>
                @endif
            </div>

            <div class="modal-body p-3">
                <div class="fw-bold text-dark h5 mb-1">{{ $task->taskType?->name ?? $task->title }}</div>
                <div class="small text-muted mb-3">{{ $task->property_name_snapshot ?? $task->property?->name }} ({{ $task->property?->property_code ?? 'Z218' }}) — {{ floor(($task->estimated_duration_minutes ?? 80) / 60) > 0 ? floor(($task->estimated_duration_minutes ?? 80) / 60).'H ' : '' }}{{ ($task->estimated_duration_minutes ?? 80) % 60 }}M</div>

                @php
                    $groupedSections = $task->checklistSnapshot->groupBy('section_name');
                @endphp

                <!-- Single-line Horizontal Scrollable Category Tab Header (First Category Active by Default) — sticky within modal body -->
                <div class="evidence-category-tabs px-1 py-2 border-bottom bg-white d-flex gap-2 overflow-auto mb-3 req-cat-sticky" style="white-space: nowrap; scrollbar-width: none;">
                    @foreach($groupedSections as $secName => $items)
                        <button type="button" class="btn btn-sm {{ $loop->first ? 'btn-primary active' : 'btn-outline-secondary' }} rounded-pill px-3 py-1 req-cat-tab" data-category="{{ Str::slug($secName ?: 'General') }}">
                            {{ $secName ?: 'General' }} ({{ count($items) }})
                        </button>
                    @endforeach
                </div>

                <!-- Requirements List Grouped by Category Section -->
                @foreach($groupedSections as $secName => $items)
                    <div class="mb-4 req-sec-group" data-category="{{ Str::slug($secName ?: 'General') }}" style="{{ $loop->first ? '' : 'display: none;' }}">
                        @foreach($items as $reqItem)
                            @php
                                $reqPhotos = is_array($reqItem->photo_url) ? $reqItem->photo_url : (!empty($reqItem->photo_url) ? [$reqItem->photo_url] : []);
                            @endphp
                            <div class="req-item-card" data-req-id="{{ $reqItem->id }}"
                                 data-has-photo="{{ count($reqPhotos) ? '1' : '0' }}"
                                 data-has-comment="{{ !empty($reqItem->comment) ? '1' : '0' }}">
                                <div class="req-item-text">
                                    - {{ $reqItem->item_label }}
                                </div>

                                <!-- Card-level photo thumbs (8 per row, synced from photo modal) -->
                                <div class="req-card-photo">
                                    @foreach($reqPhotos as $photoUrl)
                                        <img src="{{ $photoUrl }}" alt="Requirement photo" class="req-photo-thumb"
                                             data-photo-src="{{ $photoUrl }}">
                                    @endforeach
                                </div>

                                <!-- Requirement comment text preview display -->
                                <div class="req-card-comment-wrap {{ !empty($reqItem->comment) ? '' : 'd-none' }} mb-2">
                                    <div class="p-2 bg-light rounded text-secondary small d-flex align-items-start gap-2 border">
                                        <i class="bi bi-chat-left-text-fill text-primary mt-1 flex-shrink-0" style="font-size: 0.8rem;"></i>
                                        <span class="req-card-comment-text flex-grow-1" style="font-size: 0.82rem; word-break: break-word;">{{ $reqItem->comment }}</span>
                                    </div>
                                </div>

                                <!-- Checklist btn left, comment + camera icons right -->
                                <div class="d-flex align-items-center justify-content-between">
                                    @if($canEdit)
                                        <button type="button" class="checklist-action-btn btn-req-toggle {{ !empty($reqItem->completed_at) ? 'active' : '' }}"
                                                data-photo-required="{{ !empty($reqItem->is_photo_required) ? '1' : '0' }}"
                                                data-comment-required="{{ !empty($reqItem->is_comment_required) ? '1' : '0' }}">
                                            <i class="bi bi-check-lg me-1"></i>Checklist
                                        </button>
                                    @endif
                                    @if($canEdit)
                                        <div class="d-flex align-items-center gap-1 ms-auto">
                                            <button type="button" class="req-icon-btn req-comment-open" title="Comment"
                                                    data-req-id="{{ $reqItem->id }}" data-comment="{{ $reqItem->comment }}">
                                                <i class="bi {{ !empty($reqItem->comment) ? 'bi-chat-fill text-primary' : 'bi-chat' }}"></i>
                                            </button>
                                            <button type="button" class="req-icon-btn req-photo-open" title="Photos"
                                                    data-req-id="{{ $reqItem->id }}" data-photo="{{ count($reqPhotos) ? '1' : '' }}">
                                                <i class="bi {{ count($reqPhotos) ? 'bi-camera-fill text-primary' : 'bi-camera' }}"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-1 ms-auto">
                                            @if(!empty($reqItem->comment))
                                                <span class="req-icon-btn" title="Comment"><i class="bi bi-chat-fill text-primary"></i></span>
                                            @endif
                                            @if(count($reqPhotos))
                                                <span class="req-icon-btn" title="Photos"><i class="bi bi-camera-fill text-primary"></i></span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Footer Utilities -->
            <div class="modal-footer bg-light justify-content-between py-2">
                <div class="d-flex gap-3">
                    <span class="text-primary small fw-bold"><i class="bi bi-wrench me-1"></i>Issues</span>
                    <span class="text-secondary small fw-bold"><i class="bi bi-arrows-collapse me-1"></i>Collapse</span>
                </div>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input" type="checkbox" id="hideCompletedCheck">
                    <label class="form-check-label small text-muted" for="hideCompletedCheck">Hide completed</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirement Comment Popup (stacked overlay inside parent modal) -->
    <div class="req-stack-overlay" id="reqCommentModal" hidden>
        <div class="req-stack-card" role="dialog" aria-modal="true" aria-labelledby="reqCommentModalLabel">
            <div class="req-stack-head">
                <h5 class="req-stack-title" id="reqCommentModalLabel">Comment</h5>
                <button type="button" class="btn-close req-stack-close" aria-label="Close"></button>
            </div>
            <div class="req-stack-body">
                <textarea id="req-comment-text" class="form-control" rows="3" placeholder="Enter comment…"></textarea>
                <div class="extra-small text-success fw-bold mt-2 d-none" id="req-comment-saved"><i class="bi bi-check2-circle me-1"></i>Saved</div>
            </div>
            <div class="req-stack-foot">
                <button type="button" class="btn btn-primary btn-sm" id="btn-req-comment-save">
                    <i class="bi bi-check2 me-1"></i>Save
                </button>
            </div>
        </div>
    </div>

    <!-- Requirement Photo Manager Popup (stacked overlay inside parent modal) -->
    <div class="req-stack-overlay" id="reqPhotoModal" hidden>
        <div class="req-stack-card req-stack-card-lg" role="dialog" aria-modal="true" aria-labelledby="reqPhotoModalLabel">
            <div class="req-stack-head">
                <h5 class="req-stack-title" id="reqPhotoModalLabel">Photos</h5>
                <div class="d-flex gap-1 ms-auto">
                    <label class="btn btn-outline-primary btn-sm rounded-pill mb-0 px-3 req-photo-add-btn">
                        <i class="bi bi-images me-1"></i>Gallery
                        <input type="file" id="req-photo-gallery" class="visually-hidden" accept="image/*" multiple>
                    </label>
                    <label class="btn btn-primary btn-sm rounded-pill mb-0 px-3 req-photo-add-btn">
                        <i class="bi bi-camera me-1"></i>Camera
                        <input type="file" id="req-photo-camera" class="visually-hidden" accept="image/*" capture="environment">
                    </label>
                    <button type="button" class="btn-close ms-2 req-stack-close" aria-label="Close"></button>
                </div>
            </div>
            <div class="req-stack-body">
                <div class="req-photo-manager-list d-flex flex-wrap gap-2 mb-3"></div>
                <div class="extra-small text-success fw-bold d-none" id="req-photo-status"><i class="bi bi-check2-circle me-1"></i>Saved</div>
            </div>
        </div>
    </div>

    <!-- Requirement Photo Lightbox Child Modal (Prev/Next, Swipe, Close & Counter) -->
    <div class="req-stack-overlay" id="reqLightboxModal" hidden style="z-index: 1090; background: rgba(10, 15, 25, 0.94); backdrop-filter: blur(6px);">
        <div class="d-flex flex-column h-100 w-100 justify-content-between p-2 p-sm-3 position-relative" role="dialog" aria-modal="true" aria-label="Photo Preview Lightbox">
            <!-- Top bar -->
            <div class="d-flex justify-content-between align-items-center w-100 px-2 py-1" style="z-index: 30;">
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

                <img src="" alt="Requirement Photo Preview" id="lightbox-main-img" class="shadow-lg user-select-none" style="max-height: 75vh; max-width: 90vw; object-fit: contain; border-radius: 8px;">

                <button type="button" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute end-0 me-2 me-sm-4 p-0 d-flex align-items-center justify-content-center shadow-lg border border-secondary" id="lightbox-next-btn" style="width: 46px; height: 46px; z-index: 25;" aria-label="Next photo">
                    <i class="bi bi-chevron-right fs-4"></i>
                </button>
            </div>

            <!-- Bottom hint bar -->
            <div class="d-flex justify-content-center text-white-50 extra-small text-center py-1">
                <span><i class="bi bi-arrows-expand me-1"></i>Swipe left/right or use keyboard arrows &larr; &rarr; to browse</span>
            </div>
        </div>
    </div>
</div>

<!-- Task Attachments Popup Modal -->
<div class="modal fade" id="attachmentsModal" tabindex="-1" aria-labelledby="attachmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom py-2">
                <button type="button" class="btn-close ms-0 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title h6 fw-bold text-dark mb-0" id="attachmentsModalLabel">Task Attachments & Evidence Photos</h5>
            </div>
            <div class="modal-body p-3">
                @include('partials.evidence-upload', ['task' => $task, 'canEdit' => $canEdit])
            </div>
        </div>
    </div>
</div>

<!-- Task Comments Popup Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom py-2">
                <button type="button" class="btn-close ms-0 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <h5 class="modal-title h6 fw-bold text-dark mb-0" id="commentsModalLabel">Task Comments</h5>
            </div>

            <div class="modal-body p-3">
                <div id="comments-list">
                    @forelse($task->comments as $c)
                        <div class="comment-bubble">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small">{{ $c->user?->name ?? 'Cleaner' }}</span>
                                <span class="extra-small text-muted mono">{{ $c->created_at->format('M j, H:i') }}</span>
                            </div>
                            <div class="small text-secondary">{{ $c->comment }}</div>
                        </div>
                    @empty
                        @foreach($task->history->whereNotNull('remarks') as $h)
                            <div class="comment-bubble">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">{{ $h->user?->name ?? 'System' }}</span>
                                    <span class="extra-small text-muted mono">{{ $h->created_at->format('M j, H:i') }}</span>
                                </div>
                                <div class="small text-secondary">{{ $h->remarks }}</div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>

            @if($canEdit)
            <div class="modal-footer bg-light p-2">
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" id="new-comment-input" class="form-control" placeholder="Write a comment or note...">
                        <button type="button" class="btn btn-primary" id="btn-post-comment">
                            <i class="bi bi-send me-1"></i>Post
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const taskId = {{ $task->id }};

            // Dynamic Live Shift Timer — pause-aware (client-side offset, no reload)
            const startedAtTime = "{{ $task->started_at?->toIso8601String() ?? ($lastPunch['punched_in_at'] ?? '') }}";
            const completedAtTime = "{{ $task->completed_at?->toIso8601String() ?? '' }}";
            const serverStatus = "{{ $task->status }}";

            let isPaused = serverStatus === 'paused';
            let timerInterval = null;

            // Server-tracked work time (worked_seconds + last_resume_at) for display baseline.
            let serverWorkedMs = {{ (int) ($task->worked_seconds ?? 0) }} * 1000;
            let resumeAnchorMs = "{{ $task->last_resume_at?->toIso8601String() }}"
                ? new Date("{{ $task->last_resume_at?->toIso8601String() }}").getTime()
                : null;

            if (!isPaused && resumeAnchorMs === null && startedAtTime) {
                resumeAnchorMs = new Date(startedAtTime).getTime();
            }

            let frozenElapsedMs = serverWorkedMs;

            function formatTimer(seconds) {
                if (seconds < 0 || isNaN(seconds)) seconds = 0;
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = Math.floor(seconds % 60);
                return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
            }

            function currentElapsedMs() {
                if (isPaused || completedAtTime) {
                    return Math.max(0, frozenElapsedMs);
                }
                if (resumeAnchorMs !== null) {
                    return Math.max(0, serverWorkedMs + (new Date().getTime() - resumeAnchorMs));
                }
                if (startedAtTime) {
                    const start = new Date(startedAtTime).getTime();
                    const end = new Date().getTime();
                    return Math.max(0, end - start);
                }
                return 0;
            }

            function renderTimer() {
                const text = formatTimer(currentElapsedMs() / 1000);
                $('#task-shift-timer, .modal-timer').text(text);
                const $timerBtn = $('#btn-running-timer');
                if ($timerBtn.length) {
                    if (isPaused) {
                        $timerBtn.removeClass('btn-outline-info text-info-emphasis')
                            .addClass('btn-outline-success text-success-emphasis')
                            .attr('title', 'Resume task');
                        $timerBtn.find('.bi').attr('class', 'bi bi-play-fill fs-4 text-success');
                    } else {
                        $timerBtn.removeClass('btn-outline-success text-success-emphasis')
                            .addClass('btn-outline-info text-info-emphasis')
                            .attr('title', 'Pause task');
                        $timerBtn.find('.bi').attr('class', 'bi bi-pause-fill fs-4 text-info');
                    }
                }
                const $pauseBtn = $('#btn-pause');
                if ($pauseBtn.length) {
                    if (isPaused) {
                        $pauseBtn.removeClass('btn-outline-info')
                            .addClass('btn-outline-success')
                            .attr('aria-label', 'Resume task')
                            .attr('title', 'Resume task');
                        $pauseBtn.find('.bi').attr('class', 'bi bi-play-fill me-0 me-sm-1');
                        $pauseBtn.find('.req-btn-txt').text('Resume');
                    } else {
                        $pauseBtn.removeClass('btn-outline-success')
                            .addClass('btn-outline-info')
                            .attr('aria-label', 'Pause task')
                            .attr('title', 'Pause task');
                        $pauseBtn.find('.bi').attr('class', 'bi bi-pause-fill me-0 me-sm-1');
                        $pauseBtn.find('.req-btn-txt').text('Pause');
                    }
                }
            }

            function startTick() {
                if (timerInterval || !startedAtTime || completedAtTime) return;
                timerInterval = setInterval(renderTimer, 1000);
            }

            function stopTick() {
                if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
            }

            function setPaused(localPaused) {
                if (localPaused && !isPaused) {
                    frozenElapsedMs = currentElapsedMs();
                } else if (!localPaused && isPaused) {
                    serverWorkedMs = frozenElapsedMs;
                    resumeAnchorMs = new Date().getTime();
                }
                isPaused = localPaused;
                if (localPaused) {
                    stopTick();
                } else {
                    startTick();
                }
                renderTimer();
            }

            function togglePause() {
                if (completedAtTime) return;
                const target = isPaused ? 'in_progress' : 'paused';
                const $timerBtn = $('#btn-running-timer');
                const $pauseBtn = $('#btn-pause');
                if ($timerBtn.length) $timerBtn.prop('disabled', true);
                if ($pauseBtn.length) $pauseBtn.prop('disabled', true);

                setPaused(!isPaused); // optimistic local update — timer responds instantly

                axios.post('{{ route('tasks.transition', $task) }}', { status: target })
                    .then(function (res) {
                        // Sync with server-tracked work time.
                        serverWorkedMs = (res.data.worked_seconds || 0) * 1000;
                        if (target === 'paused') {
                            frozenElapsedMs = serverWorkedMs;
                            resumeAnchorMs = null;
                        } else {
                            resumeAnchorMs = res.data.last_resume_at
                                ? new Date(res.data.last_resume_at).getTime()
                                : new Date().getTime();
                        }
                        renderTimer();
                        showAlert('success', target === 'paused' ? 'Task paused.' : 'Task resumed.');
                    })
                    .catch(function (err) {
                        setPaused(target !== 'paused'); // revert on failure
                        showAlert('danger', err.response?.data?.message || 'Could not update task state.');
                    })
                    .finally(function () {
                        if ($timerBtn.length) $timerBtn.prop('disabled', false);
                        if ($pauseBtn.length) $pauseBtn.prop('disabled', false);
                    });
            }

            $('#btn-pause').on('click', togglePause);
            $('#btn-running-timer').on('click', togglePause);

            renderTimer();
            if (startedAtTime && !completedAtTime && !isPaused) startTick();

            function showAlert(type, msg) {
                var $a = $('#work-alert');
                $a.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + type).text(msg).show();
                if (type === 'success') {
                    setTimeout(function () { $a.fadeOut(300); }, 4000);
                }
            }

            // Punch in & start: grab position (fallback to property coords) → geo check-in.
            $('#btn-checkin').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true);
                const oldHtml = $btn.html();
                $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Punching in…');

                const submit = function (lat, lng, acc) {
                    axios.post('{{ route('tasks.work-checkin', $task) }}', {
                        latitude: lat,
                        longitude: lng,
                        gps_accuracy_meters: acc || null
                    }).then(function (res) {
                        showAlert('success', res.data.message);
                        window.setTimeout(function () { window.location.reload(); }, 900);
                    }).catch(function (err) {
                        var msg = err.response?.data?.message || 'Punch-in failed.';
                        showAlert(err.response?.status === 403 ? 'warning' : 'danger', msg);
                        if (err.response?.data?.task_status) {
                            window.setTimeout(function () { window.location.reload(); }, 1600);
                        }
                    });
                };

                function geo() {
                    return new Promise(function (resolve, reject) {
                        if (!navigator.geolocation) { reject({ code: 2 }); return; }
                        navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 10000 });
                    });
                }

                geo().then(function (pos) {
                    submit(pos.coords.latitude, pos.coords.longitude, Math.round(pos.coords.accuracy || 0));
                }).catch(function () {
                    // GPS unavailable/denied → use the task's recorded property position.
                    submit({{ $task->latitude_snapshot ?? 'null' }}, {{ $task->longitude_snapshot ?? 'null' }}, null);
                }).finally(function () {
                    $btn.prop('disabled', false).html(oldHtml);
                });
            });

            // Complete task (+ auto submit-for-approval when the task type requires it).
            function completeTask() {
                if (!confirm('Finish this task?')) return;
                const $btn = $('#btn-complete');
                $btn.prop('disabled', true);
                $('#complete-status').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Submitting…');

                axios.post('{{ route('tasks.complete', $task) }}', {
                    remarks: $('#work-remarks').val() || ''
                }).then(function (res) {
                    $('#complete-status').text(res.data.message);
                    showAlert('success', res.data.message);
                    window.setTimeout(function () { window.location.reload(); }, 1000);
                }).catch(function (err) {
                    var data = err.response?.data || {};
                    $('#complete-status').text(data.message || 'Completion failed.');
                    showAlert('danger', (data.message || 'Completion failed.') + (data.missing ? ' — ' + data.missing.join('; ') : ''));
                }).finally(function () {
                    $btn.prop('disabled', false);
                });
            }

            $('#btn-complete').on('click', completeTask);
            $('#btn-complete-modal').on('click', completeTask);

            // Requirements Modal with # Hash Back Button Support
            const reqModalEl = document.getElementById('requirementsModal');
            const reqModal = new bootstrap.Modal(reqModalEl);

            const attachmentsModalEl = document.getElementById('attachmentsModal');
            const attachmentsModal = new bootstrap.Modal(attachmentsModalEl);

            const commentsModalEl = document.getElementById('commentsModal');
            const commentsModal = new bootstrap.Modal(commentsModalEl);

            // Stacked child popups — custom show/hide so the parent modal stays open.
            const reqCommentOverlay = document.getElementById('reqCommentModal');
            const reqPhotoOverlay = document.getElementById('reqPhotoModal');

            // Programmatic hash sets also fire popstate in some engines; guard against self-triggered events.
            let lastProgrammaticHash = null;

            function setHash(hash) {
                lastProgrammaticHash = hash;
                window.location.hash = hash;
            }

            // Prevent Bootstrap 5 focus trap from stealing focus from child overlays
            document.addEventListener('focusin', function (e) {
                if (e.target && e.target.closest && e.target.closest('.req-stack-overlay, .req-stack-card')) {
                    e.stopImmediatePropagation();
                }
            }, true);

            function showOverlay(el, hash) {
                el.hidden = false;
                setHash(hash);
                setTimeout(function () {
                    const $input = $(el).find('textarea, input:not([type=hidden])').first();
                    if ($input.length) {
                        $input.trigger('focus');
                    }
                }, 80);
            }

            function hideOverlay(el) {
                el.hidden = true;
            }

            $('.req-stack-overlay').on('click', function (e) {
                if (e.target === this) hideOverlay(this);
            });
            $('.req-stack-close').on('click', function () {
                const $overlay = $(this).closest('.req-stack-overlay');
                hideOverlay($overlay[0]);
                // If this overlay owns the current hash, go back (closes chain to parent).
                const hash = window.location.hash;
                if ((hash === '#req-comment' && $overlay.is('#reqCommentModal')) ||
                    (hash === '#req-photo' && $overlay.is('#reqPhotoModal'))) {
                    history.back();
                }
            });

            // Base modals (bootstrap) with hash chaining.
            const baseModals = [
                { el: reqModalEl, modal: reqModal, hash: '#requirements', openers: '#btn-open-requirements' },
                { el: attachmentsModalEl, modal: attachmentsModal, hash: '#attachments', openers: '#btn-open-attachments' },
                { el: commentsModalEl, modal: commentsModal, hash: '#comments', openers: '#btn-open-comments' },
            ];

            baseModals.forEach(function (m) {
                $(m.openers).on('click', function () {
                    setHash(m.hash);
                    m.modal.show();
                });

                m.el.addEventListener('hidden.bs.modal', function () {
                    if (window.location.hash === m.hash) history.back();
                });
            });

            // Comment/photo openers — never touch bootstrap parent, just stack overlay.
            $(document).on('click', '.req-comment-open', function () {
                activeCommentReqId = $(this).data('req-id');
                const existing = $(this).attr('data-comment') || $(this).data('comment') || '';
                $('#req-comment-text').val(existing);
                $('#req-comment-saved').addClass('d-none');
                showOverlay(reqCommentOverlay, '#req-comment');
            });

            $(document).on('click', '.req-photo-open', function () {
                activePhotoReqId = $(this).data('req-id');
                $('#req-photo-status').addClass('d-none');
                renderPhotoManager([]);
                axios.get('{{ url('/admin/tasks') }}/' + taskId + '/checklists/' + activePhotoReqId + '/photo')
                    .then(function (res) {
                        renderPhotoManager(res.data.photo_url || []);
                    });
                showOverlay(reqPhotoOverlay, '#req-photo');
            });

            // Chained back navigation: hash stack → close topmost first (back/forward only).
            window.addEventListener('popstate', function () {
                const hash = window.location.hash;

                // Ignore the popstate our own setHash() triggered.
                if (lastProgrammaticHash === hash) {
                    lastProgrammaticHash = null;
                    return;
                }
                lastProgrammaticHash = null;

                // Base modals hide when hash moves away from them.
                baseModals.forEach(function (m) {
                    if (hash !== m.hash) m.modal.hide();
                });

                // Child overlays hide when hash moves away; parent (#requirements) stays if still current.
                if (hash !== '#req-comment') hideOverlay(reqCommentOverlay);
                if (hash !== '#req-photo') hideOverlay(reqPhotoOverlay);
                if (hash !== '#req-lightbox') hideOverlay(reqLightboxOverlay);
            });

            // === Lightbox Child Modal Navigation & Touch Swipe for Requirement Photos ===
            let currentLightboxPhotos = [];
            let currentLightboxIndex = 0;
            const reqLightboxOverlay = document.getElementById('reqLightboxModal');

            function updateLightboxImage(index) {
                if (!currentLightboxPhotos.length) return;
                currentLightboxIndex = (index + currentLightboxPhotos.length) % currentLightboxPhotos.length;
                const src = currentLightboxPhotos[currentLightboxIndex];
                $('#lightbox-main-img').attr('src', src);
                $('#lightbox-open-full').attr('href', src);
                $('#lightbox-counter').text((currentLightboxIndex + 1) + ' / ' + currentLightboxPhotos.length);

                if (currentLightboxPhotos.length <= 1) {
                    $('#lightbox-prev-btn, #lightbox-next-btn').hide();
                } else {
                    $('#lightbox-prev-btn, #lightbox-next-btn').show();
                }
            }

            $(document).on('click', '.req-card-photo img.req-photo-thumb, .req-photo-manager-item img, [data-photo-src]', function (e) {
                e.stopPropagation();
                const $card = $(this).closest('.req-item-card');
                const clickedSrc = $(this).attr('data-photo-src') || $(this).attr('src');

                if ($card.length) {
                    currentLightboxPhotos = $card.find('.req-photo-thumb').map(function () {
                        return $(this).attr('data-photo-src') || $(this).attr('src');
                    }).get();
                } else if (activePhotoUrls && activePhotoUrls.length) {
                    currentLightboxPhotos = activePhotoUrls;
                } else {
                    currentLightboxPhotos = [clickedSrc];
                }

                currentLightboxIndex = Math.max(0, currentLightboxPhotos.indexOf(clickedSrc));
                updateLightboxImage(currentLightboxIndex);
                showOverlay(reqLightboxOverlay, '#req-lightbox');
            });

            $('#lightbox-prev-btn').on('click', function (e) {
                e.stopPropagation();
                updateLightboxImage(currentLightboxIndex - 1);
            });

            $('#lightbox-next-btn').on('click', function (e) {
                e.stopPropagation();
                updateLightboxImage(currentLightboxIndex + 1);
            });

            $('#lightbox-close-btn').on('click', function (e) {
                e.stopPropagation();
                hideOverlay(reqLightboxOverlay);
                if (window.location.hash === '#req-lightbox') history.back();
            });

            // Touch Swipe for Lightbox on Mobile/Touch devices
            let touchStartX = 0;
            let touchEndX = 0;
            const touchArea = document.getElementById('lightbox-touch-area');

            if (touchArea) {
                touchArea.addEventListener('touchstart', function (e) {
                    if (e.changedTouches && e.changedTouches.length) {
                        touchStartX = e.changedTouches[0].screenX;
                    }
                }, { passive: true });

                touchArea.addEventListener('touchend', function (e) {
                    if (e.changedTouches && e.changedTouches.length) {
                        touchEndX = e.changedTouches[0].screenX;
                        handleSwipe();
                    }
                }, { passive: true });
            }

            function handleSwipe() {
                const diff = touchEndX - touchStartX;
                if (Math.abs(diff) > 40) {
                    if (diff < 0) {
                        // Swiped Left -> Next Image
                        updateLightboxImage(currentLightboxIndex + 1);
                    } else {
                        // Swiped Right -> Previous Image
                        updateLightboxImage(currentLightboxIndex - 1);
                    }
                }
            }

            // Keyboard navigation (Arrow keys & Esc)
            $(document).on('keydown', function (e) {
                if (reqLightboxOverlay && !reqLightboxOverlay.hidden) {
                    if (e.key === 'ArrowLeft') {
                        updateLightboxImage(currentLightboxIndex - 1);
                    } else if (e.key === 'ArrowRight') {
                        updateLightboxImage(currentLightboxIndex + 1);
                    } else if (e.key === 'Escape') {
                        hideOverlay(reqLightboxOverlay);
                        if (window.location.hash === '#req-lightbox') history.back();
                    }
                }
            });

            // === Requirement comment modal (icon → popup → save, no reload) ===
            let activeCommentReqId = null;

            $('#btn-req-comment-save').on('click', function () {
                if (!activeCommentReqId) return;
                const val = $('#req-comment-text').val().trim();
                const $btn = $(this);
                $btn.prop('disabled', true);

                axios.post('{{ url('/admin/tasks') }}/' + taskId + '/checklists/' + activeCommentReqId + '/comment', { comment: val })
                    .then(function (res) {
                        const $icon = $('.req-comment-open[data-req-id="' + activeCommentReqId + '"]');
                        $icon.data('comment', val);
                        $icon.attr('data-comment', val);
                        $icon.find('i').attr('class', val ? 'bi bi-chat-fill text-primary' : 'bi bi-chat');
                        const $card = $icon.closest('.req-item-card');
                        $card.attr('data-has-comment', val ? '1' : '0');
                        $card.data('has-comment', val ? '1' : '0');

                        // Update requirement card comment preview display
                        const $commentWrap = $card.find('.req-card-comment-wrap');
                        const $commentText = $card.find('.req-card-comment-text');
                        if (val) {
                            $commentText.text(val);
                            $commentWrap.removeClass('d-none');
                        } else {
                            $commentText.text('');
                            $commentWrap.addClass('d-none');
                        }

                        $('#req-comment-saved').removeClass('d-none');
                        setTimeout(function () {
                            hideOverlay(reqCommentOverlay);
                            if (window.location.hash === '#req-comment') {
                                history.back();
                            }
                        }, 700);
                    })
                    .catch(function (err) {
                        alert(err.response?.data?.message || 'Failed to save comment.');
                    })
                    .finally(function () {
                        $btn.prop('disabled', false);
                    });
            });

            // === Requirement photo manager modal (icon → popup → manage, no reload) ===
            let activePhotoReqId = null;
            let activePhotoUrls = [];

            function renderCardPhoto(reqId) {
                const $card = $('.req-item-card[data-req-id="' + reqId + '"]');
                const $icon = $card.find('.req-photo-open');
                axios.get('{{ url('/admin/tasks') }}/' + taskId + '/checklists/' + reqId + '/photo')
                    .then(function (res) {
                        const urls = res.data.photo_url || [];
                        const $thumbWrap = $card.find('.req-card-photo');
                        $thumbWrap.html(urls.map(function (url) {
                            return '<img src="' + url + '" alt="Requirement photo" class="req-photo-thumb" data-photo-src="' + url + '">';
                        }).join(''));
                        $icon.data('photo', urls.length ? '1' : '');
                        $icon.find('i').attr('class', urls.length ? 'bi bi-camera-fill text-primary' : 'bi bi-camera');
                    });
            }

            function renderPhotoManager(urls) {
                activePhotoUrls = urls || [];
                const $list = $('.req-photo-manager-list');
                $list.empty();
                activePhotoUrls.forEach(function (url, idx) {
                    const $item = $(
                        '<div class="req-photo-manager-item">' +
                        '<img src="' + url + '" alt="Photo" data-photo-src="' + url + '">' +
                        '<button type="button" class="btn-del-photo" title="Delete photo" data-index="' + idx + '"><i class="bi bi-x"></i></button>' +
                        '</div>'
                    );
                    $list.append($item);
                });
                if (!activePhotoUrls.length) {
                    $list.html('<p class="text-muted small mb-0">No photos yet. Add from gallery or camera.</p>');
                }
            }

            function uploadPhotoFiles(files) {
                if (!files || !files.length || !activePhotoReqId) return;
                const file = files[0];
                const formData = new FormData();
                formData.append('photo', file);

                axios.post('/admin/tasks/' + taskId + '/checklists/' + activePhotoReqId + '/upload-photo', formData)
                    .then(function (res) {
                        renderPhotoManager(res.data.photo_url || []);
                        $('#req-photo-status').removeClass('d-none');
                        renderCardPhoto(activePhotoReqId);
                    })
                    .catch(function (err) {
                        alert(err.response?.data?.message || 'Upload failed.');
                    })
                    .finally(function () {
                        if (files.length > 1) uploadPhotoFiles(files.slice(1));
                    });
            }

            $('#req-photo-gallery, #req-photo-camera').on('change', function () {
                const files = Array.from(this.files || []);
                this.value = '';
                uploadPhotoFiles(files);
            });

            $(document).on('click', '.req-photo-manager-item .btn-del-photo', function () {
                if (!activePhotoReqId) return;
                if (!confirm('Remove this photo?')) return;
                const idx = $(this).data('index');

                axios.post('/admin/tasks/' + taskId + '/checklists/' + activePhotoReqId + '/delete-photo', { index: idx })
                    .then(function (res) {
                        renderPhotoManager(res.data.photo_url || []);
                        $('#req-photo-status').removeClass('d-none');
                        renderCardPhoto(activePhotoReqId);
                    })
                    .catch(function (err) {
                        alert(err.response?.data?.message || 'Delete failed.');
                    });
            });

            // Post New Comment
            $('#btn-post-comment').on('click', function () {
                const commentText = $('#new-comment-input').val().trim();
                if (!commentText) return;

                const $btn = $(this);
                $btn.prop('disabled', true);

                axios.post('/admin/tasks/' + taskId + '/comments', { comment: commentText })
                    .then(function (res) {
                        $('#new-comment-input').val('');
                        const newBubble = `
                            <div class="comment-bubble">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">${res.data.user_name}</span>
                                    <span class="extra-small text-muted mono">${res.data.created_at}</span>
                                </div>
                                <div class="small text-secondary">${res.data.comment}</div>
                            </div>
                        `;
                        $('#comments-list').prepend(newBubble);
                        const curVal = parseInt($('#comments-counter-val').text()) || 0;
                        $('#comments-counter-val').text(curVal + 1);
                    })
                    .catch(function (err) {
                        alert(err.response?.data?.message || 'Failed to post comment.');
                    })
                    .finally(function () {
                        $btn.prop('disabled', false);
                    });
            });

            // Category Tab Filtering inside Requirements Modal (First tab active by default)
            $('.req-cat-tab').on('click', function () {
                $('.req-cat-tab').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');

                const cat = $(this).data('category');
                $('.req-sec-group').hide();
                $('.req-sec-group[data-category="' + cat + '"]').show();
            });

            // Dynamic Real-time "Hide Completed" Filter
            function applyHideCompletedFilter() {
                const hideCompleted = $('#hideCompletedCheck').is(':checked');
                $('.req-item-card').each(function () {
                    const isCompleted = $(this).find('.btn-req-toggle').hasClass('active');
                    if (hideCompleted && isCompleted) {
                        $(this).slideUp(150);
                    } else {
                        $(this).slideDown(150);
                    }
                });
            }

            $('#hideCompletedCheck').on('change', function () {
                applyHideCompletedFilter();
            });

            // Toggle Requirement Checklist Item with Check-in Gate, Loading Spinner & Mandatory Validation
            const isUserCheckedIn = {{ (!empty($lastPunch) || !empty($task->started_at) || in_array($task->status, ['in_progress', 'paused', 'completed', 'submitted_for_approval'], true)) ? 'true' : 'false' }};

            $(document).on('click', '.btn-req-toggle', function () {
                const $btn = $(this);
                const $card = $btn.closest('.req-item-card');

                // Enforce check-in requirement: cannot complete checklist without check-in
                if (!isUserCheckedIn) {
                    alert('Please punch in / check in first before checking requirements.');
                    return;
                }

                const reqId = $card.data('req-id');
                const photoRequired = $btn.data('photo-required') == '1' || $btn.attr('data-photo-required') == '1';
                const commentRequired = $btn.data('comment-required') == '1' || $btn.attr('data-comment-required') == '1';
                const hasUploadedPhoto = $card.attr('data-has-photo') == '1' || $card.find('.req-card-photo img').length > 0;
                const hasComment = $card.attr('data-has-comment') == '1' || Boolean($card.find('.req-comment-open').attr('data-comment') || $card.find('.req-comment-open').data('comment'));

                // Check mandatory conditions when toggling to completed
                if (!$btn.hasClass('active')) {
                    if (photoRequired && !hasUploadedPhoto) {
                        alert('Photo attachment is required before marking this checklist item as completed. Please click the camera icon to upload.');
                        return;
                    }
                    if (commentRequired && !hasComment) {
                        alert('Comment / details input is required before marking this checklist item as completed. Please click the chat icon to enter details.');
                        return;
                    }
                }

                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving…');

                axios.post('{{ url('/admin/tasks') }}/' + taskId + '/checklists/' + reqId + '/toggle')
                    .then(function (res) {
                        $btn.toggleClass('active', res.data.completed);
                        let curDone = parseInt($('#req-counter-val').text().split('/')[0]) || 0;
                        let totalReq = parseInt($('#req-counter-val').text().split('/')[1]) || 0;
                        if (res.data.completed) {
                            curDone++;
                        } else {
                            curDone = Math.max(0, curDone - 1);
                        }
                        $('#req-counter-val').text(curDone + '/' + totalReq);

                        // Re-apply Hide Completed filter dynamically in real-time
                        applyHideCompletedFilter();
                    })
                    .catch(function (err) {
                        alert(err.response?.data?.message || 'Failed to update requirement status.');
                    })
                    .finally(function () {
                        $btn.prop('disabled', false).html(originalHtml);
                    });
            });

            @include('partials.evidence-upload-js', ['task' => $task])
        });
    </script>
@endpush
