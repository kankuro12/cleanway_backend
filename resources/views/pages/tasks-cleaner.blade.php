@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Mine</span>
            <h2 class="h3 mt-1 mb-0">My tasks</h2>
        </div>
        <a href="{{ route('calendar') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>Calendar
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <ul class="nav nav-tabs mb-3 reveal" style="--d: 80ms" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'current' ? 'active' : '' }}" href="{{ route('tasks.my', ['tab' => 'current']) }}" role="tab">
                Current tasks <span class="badge text-bg-secondary ms-1">{{ $current->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab === 'finished' ? 'active' : '' }}" href="{{ route('tasks.my', ['tab' => 'finished']) }}" role="tab">
                Finished tasks <span class="badge text-bg-secondary ms-1">{{ $finished->total() }}</span>
            </a>
        </li>
    </ul>

    @php $tasks = $tab === 'finished' ? $finished : $current; @endphp

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>When</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td data-label="Task">
                                <span class="fw-semibold text-dark">{{ $task->title }}</span><br>
                                <small class="text-muted mono">{{ $task->reference_number }} · {{ $task->property_name_snapshot ?? 'One-off location' }}</small>
                            </td>
                            <td data-label="When">
                                {{ $task->scheduled_start_at?->format('D j M H:i') }}
                                @if($task->scheduled_end_at)<br><small class="text-muted">→ {{ $task->scheduled_end_at->format('H:i') }}</small>@endif
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $task->status === 'approved' || $task->status === 'completed' ? 'active' : (in_array($task->status, ['in_progress', 'accepted']) ? 'warning' : (in_array($task->status, ['cancelled', 'rejected']) ? 'danger' : 'muted')) }}">
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </td>
                            <td data-label="Priority"><span class="status-badge status-{{ $task->priority === 'critical' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'muted') }}">{{ $task->priority }}</span></td>
                            <td class="text-end" data-label="Actions">
                                <div class="d-flex gap-1 justify-content-end">
                                    @include('partials.directions-button', ['task' => $task])
                                    <a href="{{ route('tasks.work', $task) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-play me-1" aria-hidden="true"></i>Work
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span>
                                    {{ $tab === 'finished' ? 'Nothing finished yet.' : 'No current tasks — check the calendar.' }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $tasks->links() }}</div>
@endsection
