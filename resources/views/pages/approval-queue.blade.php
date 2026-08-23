@extends('layouts.app')

@section('title', 'Approval Queue')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Approval</span>
            <h1 class="h3 mt-1 mb-0">Approval queue</h1>
        </div>
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Task register
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm reveal" style="--d: 100ms">
        <div class="d-lg-none p-3 d-flex flex-column gap-2">
            @forelse ($tasks as $task)
                <div class="mobile-task-card compact">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                        <span class="mtc-title">{{ $task->title }}</span>
                        <span class="status-badge status-{{ in_array($task->status, ['submitted_for_approval', 'correction_requested']) ? 'warning' : 'danger' }}">
                            {{ str_replace('_', ' ', $task->status) }}
                        </span>
                    </div>
                    <div class="mtc-ref mb-1">{{ $task->reference_number }} · {{ $task->property_name_snapshot ?? '—' }}</div>
                    <div class="mtc-meta mb-2">
                        @foreach ($task->assignments as $assignment)
                            {{ $assignment->assignee?->name ?? '#' . $assignment->assignee_id }}@if(!$loop->last), @endif
                        @endforeach
                        @if($task->approvals->first())
                            · {{ $task->approvals->first()->action }} {{ $task->approvals->first()->created_at?->diffForHumans() }}
                        @endif
                    </div>
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('approvals.decide', $task) }}" class="d-grid">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-success btn-touch">
                                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Approve
                            </button>
                        </form>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('approvals.decide', $task) }}" class="flex-fill">
                                @csrf
                                <input type="hidden" name="action" value="request_correction">
                                <button class="btn btn-outline-warning btn-touch w-100">
                                    <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Ask fix
                                </button>
                            </form>
                            <form method="POST" action="{{ route('approvals.decide', $task) }}" class="flex-fill">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button class="btn btn-outline-danger btn-touch w-100">
                                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Reject
                                </button>
                            </form>
                        </div>
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary btn-touch">
                            <i class="bi bi-eye me-1" aria-hidden="true"></i>Open task
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                    Nothing awaiting approval.
                </div>
            @endforelse
        </div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th>Last review</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark">{{ $task->title }}</span><br>
                                <small class="text-muted mono">{{ $task->reference_number }} · {{ $task->property_name_snapshot ?? '—' }}</small>
                            </td>
                            <td>
                                <span class="status-badge status-{{ in_array($task->status, ['submitted_for_approval', 'correction_requested']) ? 'warning' : 'danger' }}">
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </td>
                            <td class="small">
                                @foreach ($task->assignments as $assignment)
                                    {{ $assignment->assignee?->name ?? '#' . $assignment->assignee_id }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td class="small text-muted">
                                @if($task->approvals->first())
                                    {{ $task->approvals->first()->action }} · {{ $task->approvals->first()->reviewer?->name }} · {{ $task->approvals->first()->created_at?->diffForHumans() }}
                                @else
                                    none yet
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <form method="POST" action="{{ route('approvals.decide', $task) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button class="btn btn-sm btn-outline-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.decide', $task) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="request_correction">
                                        <button class="btn btn-sm btn-outline-warning">Ask fix</button>
                                    </form>
                                    <form method="POST" action="{{ route('approvals.decide', $task) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                                    Nothing awaiting approval.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $tasks->links() }}</div>
@endsection
