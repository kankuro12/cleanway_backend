@extends('layouts.app')

@section('title', 'Task Work Sheet — Excel View')

@push('styles')
    <!-- Select2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Fullscreen-feel Excel Sheet Container */
        .worksheet-container {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 100px);
            margin: -0.75rem -0.75rem 0 -0.75rem;
            background: #ffffff;
        }

        /* Sticky Header & Toolbar */
        .worksheet-toolbar {
            position: sticky;
            top: 56px;
            z-index: 20;
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 14px;
        }

        .worksheet-formula-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 4px 10px;
            font-family: var(--font-mono, monospace);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Select2 Excel Toolbar Overrides */
        .select2-container .select2-selection--multiple {
            min-height: 28px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            padding: 1px 4px !important;
            font-size: 0.8125rem !important;
            background: #ffffff !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #e2e8f0 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 0 4px !important;
            font-size: 0.6875rem !important;
            font-family: var(--font-mono, monospace);
            color: #1e293b !important;
            line-height: 18px !important;
            margin-top: 2px !important;
            margin-bottom: 2px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #ef4444 !important;
            margin-right: 3px !important;
        }

        /* Spreadsheet Grid Table (Single Line, No Text Wrap) */
        .excel-table-scroll {
            flex: 1 1 auto;
            overflow: auto;
            max-height: calc(100vh - 215px);
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
            margin: 0;
            table-layout: auto;
        }

        .excel-table th, 
        .excel-table td {
            white-space: nowrap !important;
            vertical-align: middle;
            padding: 3px 8px !important;
            height: 28px !important;
            color: #1e293b;
            border: 1px solid #e2e8f0;
        }

        .excel-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f1f5f9;
            color: #334155;
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #cbd5e1;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .excel-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }

        .excel-table tbody tr:hover td {
            background: #f1f5f9 !important;
            cursor: default;
        }

        .excel-table tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: #e2e8f0 !important;
            font-family: var(--font-mono, monospace);
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            padding: 6px 8px !important;
            color: #0f172a;
        }

        /* Column Specific Styling */
        .col-idx { width: 42px; text-align: center; font-family: var(--font-mono, monospace); color: #64748b; background: #f8fafc !important; }
        .col-mono { font-family: var(--font-mono, monospace); font-size: 0.75rem; }
        .col-time { font-family: var(--font-mono, monospace); font-size: 0.75rem; text-align: right; }
        .col-center { text-align: center; }

        /* Compact Badges with Status Background Colors */
        .excel-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 0.6875rem;
            font-family: var(--font-mono, monospace);
            font-weight: 600;
            border-radius: 3px;
            border: 1px solid transparent;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .excel-badge-status-assigned { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
        .excel-badge-status-accepted { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
        .excel-badge-status-in_progress { background: #fef3c7; color: #92400e; border-color: #fde68a; font-weight: 700; }
        .excel-badge-status-paused { background: #ffedd5; color: #c2410c; border-color: #fed7aa; }
        .excel-badge-status-completed { background: #dcfce7; color: #15803d; border-color: #bbf7d0; font-weight: 700; }
        .excel-badge-status-approved { background: #ecfdf5; color: #047857; border-color: #a7f3d0; font-weight: 700; }
        .excel-badge-status-rejected { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
        .excel-badge-status-cancelled { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; }

        /* Compact Toolbar Controls */
        .form-control-xs, .form-select-xs {
            height: 28px !important;
            padding: 2px 6px !important;
            font-size: 0.8125rem !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
        }
        .btn-xs {
            padding: 2px 8px !important;
            font-size: 0.75rem !important;
            height: 28px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
        }

        @media (max-width: 991.98px) {
            .worksheet-container {
                margin: -0.5rem -0.5rem 0 -0.5rem;
            }
            .worksheet-toolbar {
                top: 0;
            }
            .excel-table-scroll {
                max-height: calc(100vh - 270px);
            }
        }

        /* Mobile: slim the toolbar and collapse the summary so the sheet gets the room */
        @media (max-width: 575.98px) {
            .worksheet-toolbar { padding: 6px 10px; }
            .worksheet-toolbar .eyebrow { display: none; }
            .worksheet-toolbar .form-label { font-size: 0.68rem; margin-bottom: 0; }
            .worksheet-formula-bar { gap: 8px; }
            .worksheet-formula-bar .formula-summary { display: none; }
            .worksheet-formula-bar .ms-auto { flex: 1 1 auto; }
            .excel-table-scroll { max-height: calc(100vh - 180px); }
        }
    </style>
@endpush

@section('content')
<div class="worksheet-container">
    <!-- Top Filter Toolbar -->
    <div class="worksheet-toolbar shadow-sm">
        <form method="GET" action="{{ route('tasks.worksheet') }}" id="worksheet-filter-form">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="eyebrow text-muted mb-0">Tasks · Work Sheet</span>
                    <h1 class="h5 mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-1"></i>Task Work Sheet</h1>
                    <span class="badge bg-secondary mono ms-1">{{ count($tasks) }} tasks</span>
                </div>

                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <a href="{{ route('tasks') }}" class="btn btn-xs btn-outline-secondary" aria-label="Task register">
                        <i class="bi bi-card-list"></i><span class="d-none d-sm-inline"> Task Register</span>
                    </a>
                    <button type="button" class="btn btn-xs btn-success fw-semibold" id="btn-export-excel" title="Export current sheet into Excel (.xlsx)" aria-label="Export Excel">
                        <i class="bi bi-file-earmark-excel-fill"></i><span class="d-none d-sm-inline"> Export Excel</span>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="window.print()" title="Print worksheet" aria-label="Print worksheet">
                        <i class="bi bi-printer"></i><span class="d-none d-sm-inline"> Print</span>
                    </button>
                </div>
            </div>

            <!-- Filter Controls Row (Start Date, End Date, Personnel Select2, Status) -->
            <div class="row g-2 align-items-end">
                <!-- Start Date -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label for="start_date" class="form-label mono text-xs text-muted mb-0">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control form-control-xs mono">
                </div>

                <!-- End Date -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label for="end_date" class="form-label mono text-xs text-muted mb-0">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control form-control-xs mono">
                </div>

                <!-- Personnel Multi-Select (Select2) -->
                <div class="col-12 col-sm-8 col-md-4">
                    <label for="personnel_ids" class="form-label mono text-xs text-muted mb-0">Personnel / Cleaners</label>
                    <select name="personnel_ids[]" id="personnel_ids" class="form-select form-select-xs" multiple="multiple" style="width: 100%;">
                        @foreach ($personnelList as $p)
                            <option value="{{ $p->id }}" @selected(in_array($p->id, $selectedPersonnelIds))>
                                {{ $p->name }} ({{ ucfirst($p->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label for="status" class="form-label mono text-xs text-muted mb-0">Status</label>
                    <select name="status" id="status" class="form-select form-select-xs mono">
                        <option value="all" @selected($selectedStatus === 'all')>All Statuses</option>
                        <option value="assigned" @selected($selectedStatus === 'assigned')>Assigned</option>
                        <option value="accepted" @selected($selectedStatus === 'accepted')>Accepted</option>
                        <option value="in_progress" @selected($selectedStatus === 'in_progress')>In Progress</option>
                        <option value="paused" @selected($selectedStatus === 'paused')>Paused</option>
                        <option value="completed" @selected($selectedStatus === 'completed')>Completed</option>
                        <option value="approved" @selected($selectedStatus === 'approved')>Approved</option>
                        <option value="rejected" @selected($selectedStatus === 'rejected')>Rejected</option>
                        <option value="cancelled" @selected($selectedStatus === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="col-6 col-sm-8 col-md-2 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-primary btn-xs flex-fill fw-bold">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-xs" id="btn-quick-today" title="Filter Today">Today</button>
                    <button type="button" class="btn btn-outline-secondary btn-xs" id="btn-quick-week" title="Filter This Week">Week</button>
                </div>
            </div>
        </form>

        <!-- Excel Formula / Summary Bar -->
        <div class="worksheet-formula-bar mt-2">
            <div class="formula-summary">
                <span class="text-muted"><strong class="text-primary">fx</strong> Summary:</span>
                <span><i class="bi bi-list-check me-1 text-primary"></i>Total Tasks: <strong>{{ $summary['total_tasks'] }}</strong></span>
                <span><i class="bi bi-check-circle-fill me-1 text-success"></i>Completed: <strong>{{ $summary['completed_tasks'] }}</strong> ({{ $summary['completion_rate'] }}%)</span>
                <span><i class="bi bi-hourglass-split me-1 text-warning"></i>In Progress / Active: <strong>{{ $summary['in_progress_tasks'] }}</strong></span>
                <span><i class="bi bi-clock-history me-1 text-info"></i>Total Worked: <strong class="text-dark">{{ $summary['total_worked_formatted'] }}</strong></span>
                <span><i class="bi bi-calendar2-range me-1 text-secondary"></i>Est Duration: <strong>{{ $summary['total_est_hours'] }} hrs</strong></span>
            </div>

            <!-- Real-time text search filter -->
            <div class="ms-auto position-relative" style="min-width: 180px;">
                <input type="text" id="sheet-search-input" class="form-control form-control-xs ps-4" placeholder="Quick search in sheet…">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 11px;"></i>
            </div>
        </div>
    </div>

    <!-- Excel Spreadsheet Table (Single Line, No Text Wrap) -->
    <div class="excel-table-scroll" id="excel-sheet-wrapper">
        <table class="excel-table" id="task-worksheet-table">
            <thead>
                <tr>
                    <th class="col-idx">SN</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Property Name</th>
                    <th>Address</th>
                    <th>Task Type</th>
                    <th>Assigned</th>
                    <th class="col-center">Status</th>
                    <th class="col-time">Worked Time</th>
                    <th class="col-center">Est. (m)</th>
                    <th class="col-center">Checklist</th>
                    <th class="col-center">Photos</th>
                    <th class="col-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    @php
                        $workedSecs = $task->worked_seconds > 0 ? $task->worked_seconds : ($task->started_at && $task->completed_at ? $task->started_at->diffInSeconds($task->completed_at) : ($task->started_at ? $task->started_at->diffInSeconds(now()) : 0));
                        $formattedWorked = sprintf('%02d:%02d:%02d', floor($workedSecs / 3600), floor(($workedSecs % 3600) / 60), $workedSecs % 60);
                        
                        $totalChecklistItems = $task->subtasks->count();
                        $completedChecklistItems = $task->subtasks->whereNotNull('completed_at')->count();
                        if ($totalChecklistItems === 0 && $task->checklistSnapshot->isNotEmpty()) {
                            $totalChecklistItems = $task->checklistSnapshot->count();
                            $completedChecklistItems = $task->checklistSnapshot->where('is_completed', true)->count();
                        }

                        $assignedNames = $task->assignments->map(fn($a) => $a->assignee?->name)->filter()->implode(', ');
                        $statusClass = 'excel-badge-status-' . $task->status;
                    @endphp
                    <tr data-task-id="{{ $task->id }}">
                        <!-- 1. SN -->
                        <td class="col-idx">{{ $loop->iteration }}</td>

                        <!-- 2. Date -->
                        <td class="col-mono">
                            {{ $task->scheduled_start_at ? $task->scheduled_start_at->toDateString() : '—' }}
                            @if($task->scheduled_start_at)
                                ({{ $task->scheduled_start_at->format('H:i') }}@if($task->scheduled_end_at)-{{ $task->scheduled_end_at->format('H:i') }}@endif)
                            @endif
                        </td>

                        <!-- 3. Client -->
                        <td>
                            {{ $task->property?->client?->name ?: ($task->property?->client?->company_name ?: '—') }}
                        </td>

                        <!-- 4. Property Name -->
                        <td class="fw-semibold text-dark">
                            <a href="{{ route('tasks.work', $task) }}" class="text-decoration-none text-dark" title="Open Task Console">
                                {{ $task->property?->name ?: ($task->property_name_snapshot ?: $task->title) }}
                            </a>
                        </td>

                        <!-- 5. Address -->
                        <td class="text-muted" title="{{ $task->property?->address ?: $task->address_snapshot }}">
                            {{ $task->property?->address ?: ($task->address_snapshot ?: '—') }}
                        </td>

                        <!-- 6. Task Type -->
                        <td>
                            <span class="badge bg-light text-secondary border extra-small">{{ $task->taskType?->name ?: 'Standard' }}</span>
                        </td>

                        <!-- 7. Assigned -->
                        <td>
                            @if($assignedNames)
                                <span>{{ $assignedNames }}</span>
                            @else
                                <span class="text-muted extra-small">Unassigned</span>
                            @endif
                        </td>

                        <!-- 8. Status (Appropriate Status Background Color) -->
                        <td class="col-center">
                            <span class="excel-badge {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </td>

                        <!-- 9. Worked Time -->
                        <td class="col-time fw-bold {{ $workedSecs > 0 ? 'text-primary' : 'text-muted' }}">
                            {{ $formattedWorked }}
                        </td>

                        <!-- 10. Est. (m) -->
                        <td class="col-center col-mono">
                            {{ $task->estimated_duration_minutes ?: '—' }}
                        </td>

                        <!-- 11. Checklist -->
                        <td class="col-center col-mono">
                            @if($totalChecklistItems > 0)
                                <span class="badge {{ $completedChecklistItems === $totalChecklistItems ? 'bg-success' : 'bg-light text-dark border' }}">
                                    {{ $completedChecklistItems }}/{{ $totalChecklistItems }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- 12. Photos -->
                        <td class="col-center col-mono">
                            @if($task->evidence->isNotEmpty())
                                <span class="badge bg-info text-dark" title="{{ $task->evidence->count() }} photos uploaded">
                                    {{ $task->evidence->count() }}
                                </span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>

                        <!-- 13. Actions -->
                        <td class="col-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('tasks.work', $task) }}" class="btn btn-xs btn-primary py-0 px-2" title="Open Work Console">
                                    <i class="bi bi-play-circle me-1"></i>Work
                                </a>
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit Task">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="empty-row">
                        <td colspan="13" class="text-center py-5 text-muted">
                            <div class="mb-2"><i class="bi bi-clipboard-x fs-2 text-muted opacity-50"></i></div>
                            <div class="fw-semibold">No tasks match the selected date range and personnel filters.</div>
                            <div class="extra-small text-muted mt-1">Try adjusting the start/end dates or clearing personnel filters above.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($tasks->isNotEmpty())
                <tfoot>
                    <tr>
                        <td class="col-idx">∑</td>
                        <td colspan="6" class="text-uppercase fw-bold">Total: {{ $summary['total_tasks'] }} Tasks ({{ $summary['completed_tasks'] }} Completed)</td>
                        <td class="col-center"></td>
                        <td class="col-time fw-bold text-primary">{{ $summary['total_worked_formatted'] }}</td>
                        <td class="col-center fw-bold">{{ round($summary['total_est_hours'] * 60) }}m</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Select2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SheetJS (xlsx) CDN -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        (function ($) {
            'use strict';

            // Initialize Select2 on Personnel Filter
            $('#personnel_ids').select2({
                placeholder: 'All Personnel / Cleaners…',
                allowClear: true,
                closeOnSelect: false,
                width: 'resolve'
            });

            // Quick Filter: Today
            $('#btn-quick-today').on('click', function () {
                const today = new Date().toISOString().split('T')[0];
                $('#start_date').val(today);
                $('#end_date').val(today);
                $('#worksheet-filter-form').submit();
            });

            // Quick Filter: This Week (Mon - Sun)
            $('#btn-quick-week').on('click', function () {
                const curr = new Date();
                const first = curr.getDate() - curr.getDay() + (curr.getDay() === 0 ? -6 : 1);
                const last = first + 6;
                const firstday = new Date(curr.setDate(first)).toISOString().split('T')[0];
                const lastday = new Date(curr.setDate(last)).toISOString().split('T')[0];
                $('#start_date').val(firstday);
                $('#end_date').val(lastday);
                $('#worksheet-filter-form').submit();
            });

            // Instant Client-Side Search Filter in Sheet
            $('#sheet-search-input').on('input', function () {
                const term = $(this).val().toLowerCase().trim();
                const $rows = $('#task-worksheet-table tbody tr').not('#empty-row');
                let matchCount = 0;

                $rows.each(function () {
                    const text = $(this).text().toLowerCase();
                    const match = !term || text.indexOf(term) !== -1;
                    $(this).toggle(match);
                    if (match) matchCount++;
                });

                if ($rows.length > 0) {
                    if (matchCount === 0) {
                        if (!$('#no-match-row').length) {
                            $('#task-worksheet-table tbody').append(
                                '<tr id="no-match-row"><td colspan="13" class="text-center py-4 text-muted">No rows match "' + term + '"</td></tr>'
                            );
                        }
                    } else {
                        $('#no-match-row').remove();
                    }
                }
            });

            // Export into Excel (.xlsx) using SheetJS
            $('#btn-export-excel').on('click', function () {
                const table = document.getElementById('task-worksheet-table');
                if (!table) return;

                // Clone table to strip out the Actions column for clean spreadsheet
                const clone = table.cloneNode(true);
                
                // Remove the last column (Actions) from the clone
                const headerRow = clone.querySelector('thead tr');
                if (headerRow && headerRow.lastElementChild) {
                    headerRow.lastElementChild.remove();
                }

                clone.querySelectorAll('tbody tr').forEach(function (tr) {
                    if (tr.id === 'no-match-row' || tr.id === 'empty-row') {
                        tr.remove();
                        return;
                    }
                    if (tr.style.display === 'none') {
                        tr.remove();
                        return;
                    }
                    if (tr.lastElementChild) {
                        tr.lastElementChild.remove();
                    }
                });

                const footerRow = clone.querySelector('tfoot tr');
                if (footerRow && footerRow.lastElementChild) {
                    footerRow.lastElementChild.remove();
                }

                // Generate Workbook using SheetJS
                const wb = XLSX.utils.table_to_book(clone, { sheet: "Work Sheet", raw: false });
                const start = $('#start_date').val() || 'start';
                const end = $('#end_date').val() || 'end';
                const fileName = 'task_worksheet_' + start + '_to_' + end + '.xlsx';

                XLSX.writeFile(wb, fileName);
            });

        })(jQuery);
    </script>
@endpush
