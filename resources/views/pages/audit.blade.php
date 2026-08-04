@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">System · Audit</span>
            <h2 class="h3 mt-1 mb-0">Audit log</h2>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3 reveal" style="--d: 80ms">
        <div class="col-md-3">
            <label for="action" class="visually-hidden">Action</label>
            <input type="search" name="action" id="action" value="{{ request('action') }}" class="form-control form-control-sm" placeholder="Action (contains)">
        </div>
        <div class="col-md-3">
            <label for="entity_type" class="visually-hidden">Entity</label>
            <input type="text" name="entity_type" id="entity_type" value="{{ request('entity_type') }}" class="form-control form-control-sm" placeholder="Entity type (e.g. task)">
        </div>
        <div class="col-md-2">
            <label for="from" class="visually-hidden">From</label>
            <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label for="to" class="visually-hidden">To</label>
            <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 120ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Source</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at?->format('j M Y H:i:s') }}</td>
                            <td>{{ $log->actor?->name ?? 'system' }}</td>
                            <td><span class="status-badge status-muted">{{ $log->action }}</span></td>
                            <td class="small">
                                {{ class_basename($log->entity_type) }} #{{ $log->entity_id ?? '—' }}
                                @if($log->before || $log->after)
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="collapse" data-bs-target="#audit-{{ $log->id }}" aria-expanded="false">
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>diff
                                    </button>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $log->source }}</td>
                            <td class="small text-muted">{{ $log->ip }}</td>
                        </tr>
                        @if($log->before || $log->after)
                            <tr class="collapse" id="audit-{{ $log->id }}">
                                <td colspan="6">
                                    <pre class="small mb-0 text-muted" style="max-height: 200px; overflow: auto">{{ json_encode(['before' => $log->before, 'after' => $log->after], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-journal-text"></i></span>
                                    No audit entries match the filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $logs->links() }}</div>
@endsection
