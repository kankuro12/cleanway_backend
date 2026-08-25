@extends('layouts.app')

@section('title', 'Payout Sheet')

@push('styles')
    <style>
        .payout-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .payout-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .payout-kpi-card {
            background: var(--cw-card-bg, #ffffff);
            border: 1px solid var(--cw-border, #e2e8f0);
            border-radius: 4px;
            padding: 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .payout-kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 3px;
            background: var(--cw-border, #cbd5e1);
        }

        .payout-kpi-card.primary::before { background: var(--cw-accent, #ff6b00); }
        .payout-kpi-card.success::before { background: var(--cw-status-active, #10b981); }
        .payout-kpi-card.info::before { background: #0284c7; }
        .payout-kpi-card.warning::before { background: #f59e0b; }

        .payout-kpi-label {
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .payout-kpi-val {
            font-family: var(--font-display, sans-serif);
            font-weight: 900;
            font-size: 1.5rem;
            line-height: 1.1;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .payout-kpi-sub {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* Filter sheet styling */
        .payout-filter-card {
            background: #ffffff;
            border: 1px solid var(--cw-border, #e2e8f0);
            border-radius: 4px;
            padding: 1rem;
        }

        /* Spreadsheet style table */
        .payout-table-wrapper {
            background: #ffffff;
            border: 1px solid var(--cw-border, #e2e8f0);
            border-radius: 4px;
            overflow-x: auto;
        }

        .payout-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.8125rem;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .payout-table th {
            background: #f8fafc;
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #475569;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #f1f5f9;
            font-weight: 700;
        }

        .payout-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .payout-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .payout-table tfoot th,
        .payout-table tfoot td {
            background: #f1f5f9;
            font-family: var(--font-mono, monospace);
            font-weight: 700;
            border-top: 2px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
        }

        .btn-preset {
            font-size: 0.6875rem;
            font-family: var(--font-mono, monospace);
            padding: 2px 6px;
            border-radius: 3px;
        }

        .avatar-tiny {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        @media print {
            .sidebar, .admin-topbar, .payout-filter-card, .btn-print-hide, .nav-tabs { display: none !important; }
            .admin-main { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
            .payout-table-wrapper { border: none !important; }
            .payout-kpi-grid { grid-template-columns: repeat(4, 1fr) !important; }
        }
    </style>
@endpush

@section('content')
<div class="payout-container">
    <!-- Header with Breadcrumb Eyebrow -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 reveal" style="--d: 40ms">
        <div>
            <span class="eyebrow"><i class="bi bi-wallet2 me-1"></i>Reports · Workforce Payouts</span>
            <h1 class="h4 fw-bold mb-0 text-dark">Payout Sheet</h1>
        </div>
        <div class="d-flex align-items-center gap-1 flex-wrap btn-print-hide">
            <button type="button" class="btn btn-sm btn-success fw-semibold" id="btn-export-payout-csv" title="Export Payout Sheet as CSV">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Print Payout Report">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-person-badge me-1"></i>My Payroll
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Ribbon -->
    <div class="payout-kpi-grid reveal" style="--d: 60ms">
        <div class="payout-kpi-card primary">
            <div class="payout-kpi-label"><i class="bi bi-cash-stack me-1"></i>Total Gross Payout</div>
            <div class="payout-kpi-val text-primary-emphasis">${{ number_format($summary['total_payout'], 2) }}</div>
            <div class="payout-kpi-sub mono">${{ number_format($summary['total_base'], 2) }} base + ${{ number_format($summary['total_extra'], 2) }} allowances</div>
        </div>
        <div class="payout-kpi-card success">
            <div class="payout-kpi-label"><i class="bi bi-clock-history me-1"></i>Total Hours Worked</div>
            <div class="payout-kpi-val">{{ number_format($summary['total_hours'], 2) }} <span class="fs-6 fw-normal text-muted">hrs</span></div>
            <div class="payout-kpi-sub">Across {{ $summary['total_tasks'] }} completed tasks</div>
        </div>
        <div class="payout-kpi-card info">
            <div class="payout-kpi-label"><i class="bi bi-people me-1"></i>Active Personnel</div>
            <div class="payout-kpi-val">{{ $summary['active_workers'] }}</div>
            <div class="payout-kpi-sub">Cleaners & supervisors</div>
        </div>
        <div class="payout-kpi-card warning">
            <div class="payout-kpi-label"><i class="bi bi-speedometer me-1"></i>Average Earning</div>
            <div class="payout-kpi-val">${{ number_format($summary['avg_hourly'], 2) }}<span class="fs-6 fw-normal text-muted">/hr</span></div>
            <div class="payout-kpi-sub">Overall effective hourly payout</div>
        </div>
    </div>

    <!-- Duration & Multi-Filter Form Card -->
    <div class="payout-filter-card shadow-sm reveal" style="--d: 80ms">
        <form method="GET" action="{{ route('reports.payouts') }}" id="payout-filter-form">
            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                <span class="mono fw-bold extra-small text-muted text-uppercase"><i class="bi bi-funnel me-1"></i>Filter Duration & Scope</span>
                <div class="d-flex gap-1 flex-wrap btn-print-hide">
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="today">Today</button>
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="yesterday">Yesterday</button>
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="this_week">This Week</button>
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="last_week">Last Week</button>
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="this_month">This Month</button>
                    <button type="button" class="btn btn-outline-secondary btn-preset" data-preset="last_month">Last Month</button>
                </div>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label for="from" class="form-label extra-small mono text-muted mb-1">From Date</label>
                    <input type="date" name="from" id="from" value="{{ $from }}" class="form-control form-control-sm mono">
                </div>
                <div class="col-6 col-md-2">
                    <label for="to" class="form-label extra-small mono text-muted mb-1">To Date</label>
                    <input type="date" name="to" id="to" value="{{ $to }}" class="form-control form-control-sm mono">
                </div>
                <div class="col-6 col-md-2">
                    <label for="user_id" class="form-label extra-small mono text-muted mb-1">Personnel / Cleaner</label>
                    <select name="user_id" id="user_id" class="form-select form-select-sm">
                        <option value="">All Personnel</option>
                        @foreach ($workers as $w)
                            <option value="{{ $w->id }}" @selected($userId == $w->id)>{{ $w->name }} ({{ $w->role == 1 ? 'Supervisor' : 'Cleaner' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="property_id" class="form-label extra-small mono text-muted mb-1">Property</label>
                    <select name="property_id" id="property_id" class="form-select form-select-sm select2-searchable">
                        <option value="">All Properties</option>
                        @foreach ($properties as $prop)
                            <option value="{{ $prop->id }}" @selected($propertyId == $prop->id)>{{ $prop->dropdown_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label extra-small mono text-muted mb-1">Task Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="completed_or_approved" @selected($status === 'completed_or_approved')>Completed & Approved</option>
                        <option value="completed" @selected($status === 'completed')>Completed only</option>
                        <option value="approved" @selected($status === 'approved')>Approved only</option>
                        <option value="all" @selected($status === 'all')>All Statuses</option>
                    </select>
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill fw-bold"><i class="bi bi-funnel-fill"></i></button>
                    <a href="{{ route('reports.payouts') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Navigation Tabs: Detailed Breakdown vs Worker Summary -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 reveal" style="--d: 100ms">
        <ul class="nav nav-tabs border-bottom-0" id="payoutTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold small py-2 px-3" id="itemized-tab" data-bs-toggle="tab" data-bs-target="#itemized-pane" type="button" role="tab" aria-controls="itemized-pane" aria-selected="true">
                    <i class="bi bi-list-columns-reverse me-1"></i>Itemized Tasks ({{ count($payoutRows) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold small py-2 px-3" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-pane" type="button" role="tab" aria-controls="summary-pane" aria-selected="false">
                    <i class="bi bi-person-lines-fill me-1"></i>Summary by Personnel ({{ count($workerSummary) }})
                </button>
            </li>
        </ul>

        <!-- Instant In-Page Search Box -->
        <div class="position-relative" style="min-width: 220px;">
            <input type="text" id="payout-live-search" class="form-control form-control-sm ps-4" placeholder="Filter rows in sheet…">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 11px;"></i>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="payoutTabContent">
        <!-- 1. Detailed Itemized Tasks Table -->
        <div class="tab-pane fade show active" id="itemized-pane" role="tabpanel" aria-labelledby="itemized-tab">
            <div class="payout-table-wrapper shadow-sm reveal" style="--d: 120ms">
                <table class="payout-table" id="payout-itemized-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date / Completed</th>
                            <th>Personnel / Cleaner</th>
                            <th>Property & Client</th>
                            <th>Task Title</th>
                            <th class="text-center">Hours</th>
                            <th>Pay Type / Rate</th>
                            <th class="text-end">Base Pay</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Gross Payout</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payoutRows as $idx => $row)
                            <tr class="payout-row">
                                <td class="mono text-muted">{{ $idx + 1 }}</td>
                                <td class="mono">{{ $row['completed_at'] ? \Carbon\Carbon::parse($row['completed_at'])->format('Y-m-d H:i') : '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        @forelse ($row['assignees'] as $assignee)
                                            <span class="d-inline-flex align-items-center gap-1 bg-light border px-1.5 py-0.5 rounded extra-small fw-semibold">
                                                <span class="avatar-tiny">{{ strtoupper(substr($assignee->name, 0, 1)) }}</span>
                                                {{ $assignee->name }}
                                            </span>
                                        @empty
                                            <span class="text-muted extra-small">Unassigned</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $row['property']?->name ?? $row['task']->property_name_snapshot ?? 'One-off location' }}</div>
                                    @if($row['property']?->property_code)
                                        <span class="badge bg-light text-secondary border mono extra-small">[{{ $row['property']->property_code }}]</span>
                                    @endif
                                    @if($row['property']?->client?->name || $row['property']?->client?->company_name)
                                        <span class="extra-small text-muted ms-1"><i class="bi bi-person me-0.5"></i>{{ $row['property']->client->name ?: $row['property']->client->company_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $row['task']->title }}</span>
                                    <small class="mono text-muted d-block">{{ $row['task']->reference_number }}</small>
                                </td>
                                <td class="text-center mono fw-bold">{{ number_format($row['hours'], 2) }}</td>
                                <td class="mono extra-small">
                                    <span class="badge bg-light text-secondary border">{{ $row['pay_type'] }}</span>
                                </td>
                                <td class="text-end mono">${{ number_format($row['base_pay'], 2) }}</td>
                                <td class="text-end mono">
                                    @if($row['extra_total'] > 0)
                                        <span class="text-success">+${{ number_format($row['extra_total'], 2) }}</span>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </td>
                                <td class="text-end mono fw-bold text-primary-emphasis">${{ number_format($row['total_payout'], 2) }}</td>
                                <td class="text-center">
                                    @include('partials.task-status-icon', ['task' => $row['task']])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x display-6 d-block mb-2 text-secondary"></i>
                                    No completed payout tasks found for the selected duration and filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($payoutRows) > 0)
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end text-uppercase">Total Summary:</th>
                                <th class="text-center mono">{{ number_format($summary['total_hours'], 2) }} hrs</th>
                                <th></th>
                                <th class="text-end mono">${{ number_format($summary['total_base'], 2) }}</th>
                                <th class="text-end mono text-success">+${{ number_format($summary['total_extra'], 2) }}</th>
                                <th class="text-end mono text-primary-emphasis fs-6">${{ number_format($summary['total_payout'], 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- 2. Summary Breakdown by Personnel Table -->
        <div class="tab-pane fade" id="summary-pane" role="tabpanel" aria-labelledby="summary-tab">
            <div class="payout-table-wrapper shadow-sm">
                <table class="payout-table" id="payout-worker-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Personnel / Worker</th>
                            <th>Role</th>
                            <th class="text-center">Tasks Completed</th>
                            <th class="text-center">Total Hours</th>
                            <th class="text-end">Base Pay</th>
                            <th class="text-end">Allowances / Extras</th>
                            <th class="text-end">Total Gross Payout</th>
                            <th class="text-end">Avg Rate / Hr</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workerSummary as $wIdx => $ws)
                            <tr class="payout-row">
                                <td class="mono text-muted">{{ $wIdx + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-tiny" style="width: 26px; height: 26px; font-size: 11px;">{{ strtoupper(substr($ws['worker']->name, 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $ws['worker']->name }}</div>
                                            <small class="text-muted mono">{{ $ws['worker']->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $ws['worker']->role == 1 ? 'warning' : 'muted' }}">
                                        {{ $ws['worker']->role == 1 ? 'Supervisor' : 'Cleaner' }}
                                    </span>
                                </td>
                                <td class="text-center mono fw-semibold">{{ $ws['task_count'] }}</td>
                                <td class="text-center mono fw-bold">{{ number_format($ws['total_hours'], 2) }} hrs</td>
                                <td class="text-end mono">${{ number_format($ws['total_base'], 2) }}</td>
                                <td class="text-end mono text-success">+${{ number_format($ws['total_extra'], 2) }}</td>
                                <td class="text-end mono fw-bold text-primary-emphasis fs-6">${{ number_format($ws['total_payout'], 2) }}</td>
                                <td class="text-end mono text-muted">
                                    ${{ $ws['total_hours'] > 0 ? number_format($ws['total_payout'] / $ws['total_hours'], 2) : '0.00' }}/hr
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    No personnel summary available for the selected duration.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($workerSummary) > 0)
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end text-uppercase">Total:</th>
                                <th class="text-center mono">{{ $summary['total_tasks'] }}</th>
                                <th class="text-center mono">{{ number_format($summary['total_hours'], 2) }} hrs</th>
                                <th class="text-end mono">${{ number_format($summary['total_base'], 2) }}</th>
                                <th class="text-end mono text-success">+${{ number_format($summary['total_extra'], 2) }}</th>
                                <th class="text-end mono text-primary-emphasis fs-6">${{ number_format($summary['total_payout'], 2) }}</th>
                                <th class="text-end mono">${{ number_format($summary['avg_hourly'], 2) }}/hr</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        // Initialize Select2 on Property dropdown
        if ($.fn.select2) {
            $('#property_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'All Properties',
                allowClear: true,
                width: '100%'
            });
        }

        // Duration Presets Helper
        function formatDate(d) {
            var month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }

        $('.btn-preset').on('click', function () {
            var preset = $(this).data('preset');
            var now = new Date();
            var from = new Date();
            var to = new Date();

            if (preset === 'today') {
                from = now;
                to = now;
            } else if (preset === 'yesterday') {
                from.setDate(now.getDate() - 1);
                to.setDate(now.getDate() - 1);
            } else if (preset === 'this_week') {
                var day = now.getDay() || 7;
                from.setDate(now.getDate() - day + 1);
                to.setDate(from.getDate() + 6);
            } else if (preset === 'last_week') {
                var day = now.getDay() || 7;
                from.setDate(now.getDate() - day - 6);
                to = new Date(from);
                to.setDate(from.getDate() + 6);
            } else if (preset === 'this_month') {
                from = new Date(now.getFullYear(), now.getMonth(), 1);
                to = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            } else if (preset === 'last_month') {
                from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                to = new Date(now.getFullYear(), now.getMonth(), 0);
            }

            $('#from').val(formatDate(from));
            $('#to').val(formatDate(to));
            $('#payout-filter-form').submit();
        });

        // Live In-Page Text Filter
        $('#payout-live-search').on('input', function () {
            var q = $(this).val().toLowerCase().trim();
            $('.payout-row').each(function () {
                var text = $(this).text().toLowerCase();
                $(this).toggle(q === '' || text.indexOf(q) !== -1);
            });
        });

        // CSV Export Trigger
        $('#btn-export-payout-csv').on('click', function () {
            var csv = [];
            var activePane = $('.tab-pane.active');
            var table = activePane.find('table');

            table.find('tr').each(function () {
                var row = [];
                $(this).find('th, td').each(function () {
                    var text = $(this).text().replace(/\s+/g, ' ').trim();
                    text = text.replace(/"/g, '""');
                    row.push('"' + text + '"');
                });
                if (row.length > 0) {
                    csv.push(row.join(','));
                }
            });

            var csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var downloadLink = document.createElement('a');
            downloadLink.download = 'payout_sheet_' + $('#from').val() + '_to_' + $('#to').val() + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    })(jQuery);
</script>
@endpush
