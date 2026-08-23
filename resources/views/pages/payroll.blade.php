@extends('layouts.app')

@section('title', 'Payroll & Earnings')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 reveal">
        <div>
            <h1 class="h3 mb-0">Payroll & Earnings</h1>
            <div class="text-muted small">Automatic task payout tracking for approved cleaner jobs</div>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-3 py-2 rounded-pill mono">
                <i class="bi bi-wallet2 me-1"></i>Cleaner Account
            </span>
        </div>
    </div>

    <!-- KPI Summary Cards (Breezeway Style) -->
    <div class="row g-3 mb-4 reveal">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Total Earned</div>
                <div class="h3 fw-bold text-success mb-0">${{ number_format($totalEarned, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Tasks Approved</div>
                <div class="h3 fw-bold text-dark mb-0">{{ $approvedCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Hours Worked</div>
                <div class="h3 fw-bold text-info mb-0">{{ number_format($totalHours, 1) }} hrs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="text-muted extra-small text-uppercase fw-bold mb-1">Extra Payments</div>
                <div class="h3 fw-bold text-primary mb-0">${{ number_format($totalExtra, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Date Navigation Tabs (TODAY, YESTERDAY, WEEK, ALL) -->
    <div class="my-tasks-tab-nav mb-4 reveal">
        <a href="{{ route('payroll.index', ['tab' => 'today']) }}" class="my-tasks-tab-item {{ $tab === 'today' ? 'active' : '' }}">
            TODAY @if($counts['today'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['today'] }}</span>@endif
        </a>
        <a href="{{ route('payroll.index', ['tab' => 'yesterday']) }}" class="my-tasks-tab-item {{ $tab === 'yesterday' ? 'active' : '' }}">
            YESTERDAY @if($counts['yesterday'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['yesterday'] }}</span>@endif
        </a>
        <a href="{{ route('payroll.index', ['tab' => 'week']) }}" class="my-tasks-tab-item {{ $tab === 'week' ? 'active' : '' }}">
            WEEK @if($counts['week'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['week'] }}</span>@endif
        </a>
        <a href="{{ route('payroll.index', ['tab' => 'all']) }}" class="my-tasks-tab-item {{ $tab === 'all' ? 'active' : '' }}">
            ALL HISTORY @if($counts['all'] > 0)<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $counts['all'] }}</span>@endif
        </a>
    </div>

    <!-- Detailed Payroll Task Items Table / List -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden reveal">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark">Approved Task Payout History</h2>
            <span class="mono extra-small text-muted">{{ count($payrollItems) }} record(s)</span>
        </div>
        <!-- Mobile Cards List View (iPhone & Small Screens < 768px) -->
        <div class="d-md-none p-3 d-flex flex-column gap-2">
            @forelse($payrollItems as $item)
                <div class="mobile-task-card compact bg-white border rounded-3 p-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-bold text-dark fs-6 mb-1">{{ $item['task']->title }}</div>
                            <div class="extra-small text-muted">
                                <i class="bi bi-building me-1 text-primary"></i>{{ $item['task']->property_name_snapshot ?? $item['task']->property?->name ?? 'Property' }}
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 mono extra-small">
                            APPROVED
                        </span>
                    </div>

                    <div class="bg-light p-2 rounded-2 mb-2 d-flex justify-content-between align-items-center mono extra-small">
                        <span class="text-muted">Rate & Hours:</span>
                        <span class="fw-semibold text-dark">${{ number_format($item['rate_per_hour'], 2) }}/hr · {{ $item['hours'] }}h</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                        <div class="extra-small text-muted">
                            Base: ${{ number_format($item['base_pay'], 2) }}
                            @if($item['extra_total'] > 0)
                                <span class="text-success ms-1">+${{ number_format($item['extra_total'], 2) }} extra</span>
                            @endif
                        </div>
                        <div class="mono fw-bold text-success fs-5">
                            ${{ number_format($item['total_payout'], 2) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary-subtle"></i>
                    No approved task earnings found for this filter period.
                </div>
            @endforelse
        </div>

        <!-- Desktop/Tablet Table View (>= 768px) -->
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle mb-0">
                <thead class="bg-light text-muted extra-small text-uppercase mono">
                    <tr>
                        <th class="ps-4">Task & Property</th>
                        <th>Cleaner Rate</th>
                        <th>Duration</th>
                        <th>Base Pay</th>
                        <th>Extra (Parking/Other)</th>
                        <th>Total Payout</th>
                        <th class="pe-4 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrollItems as $item)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $item['task']->title }}</div>
                                <div class="extra-small text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $item['task']->property_name_snapshot ?? $item['task']->property?->name ?? 'Property' }}
                                </div>
                            </td>
                            <td class="mono small">${{ number_format($item['rate_per_hour'], 2) }}/hr</td>
                            <td class="mono small">{{ $item['hours'] }} hrs ({{ $item['duration_minutes'] }}m)</td>
                            <td class="mono small fw-semibold text-dark">${{ number_format($item['base_pay'], 2) }}</td>
                            <td class="mono small text-secondary">
                                @if($item['extra_total'] > 0)
                                    <span class="text-success fw-bold">+${{ number_format($item['extra_total'], 2) }}</span>
                                    @if($item['extra_parking'] > 0)<span class="extra-small text-muted d-block">Parking: ${{ number_format($item['extra_parking'], 2) }}</span>@endif
                                @else
                                    $0.00
                                @endif
                            </td>
                            <td class="mono fw-bold text-success fs-6">${{ number_format($item['total_payout'], 2) }}</td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 mono">
                                    <i class="bi bi-check-circle-fill me-1"></i>APPROVED
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary-subtle"></i>
                                No approved task earnings found for this filter period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
