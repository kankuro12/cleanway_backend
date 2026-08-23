@extends('layouts.app')

@section('title', 'Task Types')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Config</span>
            <h1 class="h3 mt-1 mb-0">Task types</h1>
        </div>
        <a href="{{ route('checklists') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-check me-1" aria-hidden="true"></i>Checklists
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header mono">New task type</div>
        <div class="card-body">
            <form method="POST" action="{{ route('task-types.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label for="name" class="form-label visually-hidden">Name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="Name" required>
                </div>
                <div class="col-md-2">
                    <label for="default_priority" class="form-label visually-hidden">Default priority</label>
                    <select name="default_priority" id="default_priority" class="form-select form-select-sm">
                        @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                            <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="default_estimated_duration_minutes" class="form-label visually-hidden">Duration</label>
                    <input type="number" min="1" id="default_estimated_duration_minutes" name="default_estimated_duration_minutes" class="form-control form-control-sm" placeholder="Duration min">
                </div>
                <div class="col-md-3">
                    <label for="default_checklist_id" class="form-label visually-hidden">Default checklist</label>
                    <select name="default_checklist_id" id="default_checklist_id" class="form-select form-select-sm">
                        <option value="">Default checklist</option>
                        @foreach ($checklists as $checklist)
                            <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus me-1" aria-hidden="true"></i>Add type
                    </button>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label visually-hidden">Description</label>
                    <input type="text" id="description" name="description" class="form-control form-control-sm" placeholder="Description (optional)">
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Photos</th>
                        <th>Approval</th>
                        <th>Tasks</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taskTypes as $type)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('task-types.update', $type) }}" class="d-flex gap-2 flex-wrap">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $type->name }}" class="form-control form-control-sm" style="max-width: 220px" required>
                                    <select name="default_priority" class="form-select form-select-sm" style="max-width: 110px">
                                        @foreach (['low', 'medium', 'high', 'critical'] as $priority)
                                            <option value="{{ $priority }}" @selected($type->default_priority === $priority)>{{ ucfirst($priority) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" name="default_estimated_duration_minutes" value="{{ $type->default_estimated_duration_minutes }}" class="form-control form-control-sm" style="max-width: 100px" placeholder="min">
                                    <select name="default_checklist_id" class="form-select form-select-sm" style="max-width: 150px">
                                        <option value="">Checklist</option>
                                        @foreach ($checklists as $checklist)
                                            <option value="{{ $checklist->id }}" @selected($type->default_checklist_id === $checklist->id)>{{ $checklist->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="small d-flex align-items-center m-0"><input type="checkbox" name="before_photo_required" value="1" class="me-1" @checked($type->before_photo_required)>Before</label>
                                    <label class="small d-flex align-items-center m-0"><input type="checkbox" name="after_photo_required" value="1" class="me-1" @checked($type->after_photo_required)>After</label>
                                    <input type="number" min="0" name="minimum_photo_count" value="{{ $type->minimum_photo_count }}" class="form-control form-control-sm" style="max-width: 70px" title="Min photos">
                                    <label class="small d-flex align-items-center m-0"><input type="checkbox" name="approval_required" value="1" class="me-1" @checked($type->approval_required)>Approval</label>
                                    <label class="small d-flex align-items-center m-0"><input type="checkbox" name="active" value="1" class="me-1" @checked($type->active)>Active</label>
                                    <button class="btn btn-sm btn-outline-secondary" title="Save">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                    </button>
                                </form>
                                @if($type->description)<small class="text-muted d-block mt-1">{{ $type->description }}</small>@endif
                            </td>
                            <td><span class="status-badge status-{{ $type->default_priority === 'critical' ? 'danger' : 'muted' }}">{{ $type->default_priority }}</span></td>
                            <td class="small text-muted">min {{ $type->minimum_photo_count }}</td>
                            <td><span class="status-badge status-{{ $type->approval_required ? 'warning' : 'muted' }}">{{ $type->approval_required ? 'required' : '—' }}</span></td>
                            <td>{{ $type->tasks_count }}</td>
                            <td><span class="status-badge status-{{ $type->active ? 'active' : 'muted' }}">{{ $type->active ? 'active' : 'inactive' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-clipboard2-pulse"></i></span>
                                    No task types yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $taskTypes->links() }}</div>
@endsection
