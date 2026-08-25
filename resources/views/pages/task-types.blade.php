@extends('layouts.app')

@section('title', 'Task Types')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Setup</span>
            <h1 class="h3 mt-1 mb-0">Task types</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('checklists') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-card-checklist me-1" aria-hidden="true"></i>Checklists
            </a>
            @if(auth()->user()->hasPermission('4.7') || auth()->user()->hasRole(0))
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskTypeModal">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>New task type
                </button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm reveal" style="--d: 80ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Type & Description</th>
                        <th>Default Priority</th>
                        <th>Est. Duration</th>
                        <th>Default Checklist</th>
                        <th>Requirements</th>
                        <th>Tasks</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taskTypes as $type)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $type->name }}</div>
                                @if($type->description)
                                    <small class="text-muted d-block mt-0.5">{{ Str::limit($type->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge status-{{ $type->default_priority === 'critical' ? 'danger' : ($type->default_priority === 'high' ? 'warning' : ($type->default_priority === 'medium' ? 'in_progress' : 'muted')) }}">
                                    {{ ucfirst($type->default_priority) }}
                                </span>
                            </td>
                            <td class="mono small">
                                @if($type->default_estimated_duration_minutes)
                                    @php
                                        $h = floor($type->default_estimated_duration_minutes / 60);
                                        $m = $type->default_estimated_duration_minutes % 60;
                                    @endphp
                                    <span class="badge bg-light text-dark border">{{ $h > 0 ? "{$h}h " : '' }}{{ $m > 0 ? "{$m}m" : ($h === 0 ? '0m' : '') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($type->defaultChecklist)
                                    <a href="{{ route('checklists.edit', $type->defaultChecklist) }}" class="text-decoration-none small text-primary fw-medium">
                                        <i class="bi bi-card-checklist me-1"></i>{{ $type->defaultChecklist->name }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($type->before_photo_required)
                                        <span class="badge bg-secondary-subtle text-secondary extra-small" title="Before photo required">
                                            <i class="bi bi-camera me-0.5"></i>Before
                                        </span>
                                    @endif
                                    @if($type->after_photo_required)
                                        <span class="badge bg-secondary-subtle text-secondary extra-small" title="After photo required">
                                            <i class="bi bi-camera-fill me-0.5"></i>After ({{ $type->minimum_photo_count }} min)
                                        </span>
                                    @endif
                                    @if($type->approval_required)
                                        <span class="badge bg-warning-subtle text-warning-emphasis extra-small" title="Requires supervisor approval">
                                            <i class="bi bi-shield-check me-0.5"></i>Approval
                                        </span>
                                    @endif
                                    @if(!$type->before_photo_required && !$type->after_photo_required && !$type->approval_required)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border mono">{{ $type->tasks_count }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $type->active ? 'ok' : 'muted' }}">
                                    {{ $type->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-type"
                                        data-id="{{ $type->id }}"
                                        data-name="{{ $type->name }}"
                                        data-description="{{ $type->description }}"
                                        data-priority="{{ $type->default_priority }}"
                                        data-duration="{{ $type->default_estimated_duration_minutes }}"
                                        data-checklist="{{ $type->default_checklist_id }}"
                                        data-before="{{ $type->before_photo_required ? '1' : '0' }}"
                                        data-after="{{ $type->after_photo_required ? '1' : '0' }}"
                                        data-photos="{{ $type->minimum_photo_count }}"
                                        data-approval="{{ $type->approval_required ? '1' : '0' }}"
                                        data-active="{{ $type->active ? '1' : '0' }}"
                                        data-sort="{{ $type->sort_order }}"
                                        data-update-url="{{ route('task-types.update', $type) }}">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state py-5 text-center">
                                    <span class="empty-state-icon text-muted fs-1 d-block mb-2" aria-hidden="true"><i class="bi bi-clipboard2-pulse"></i></span>
                                    <h6 class="fw-bold">No task types found</h6>
                                    <p class="text-muted small">Create task types to standardize operations across properties.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 140ms">{{ $taskTypes->links() }}</div>

    <!-- Create Task Type Modal -->
    <div class="modal fade" id="createTaskTypeModal" tabindex="-1" aria-labelledby="createTaskTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('task-types.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold" id="createTaskTypeModalLabel"><i class="bi bi-plus-circle me-1 text-primary"></i>Create Task Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="create_name" class="form-label fw-semibold small">Task Type Name <span class="text-danger">*</span></label>
                                <input type="text" id="create_name" name="name" class="form-control" placeholder="e.g. Turnover Clean, Inspection, Deep Clean" required>
                            </div>
                            <div class="col-md-4">
                                <label for="create_priority" class="form-label fw-semibold small">Default Priority <span class="text-danger">*</span></label>
                                <select name="default_priority" id="create_priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="create_duration" class="form-label fw-semibold small">Est. Duration (Minutes)</label>
                                <input type="number" min="1" max="1440" id="create_duration" name="default_estimated_duration_minutes" class="form-control" placeholder="e.g. 90">
                            </div>
                            <div class="col-md-6">
                                <label for="create_checklist" class="form-label fw-semibold small">Default Checklist</label>
                                <select name="default_checklist_id" id="create_checklist" class="form-select">
                                    <option value="">None (No checklist attached)</option>
                                    @foreach ($checklists as $checklist)
                                        <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="create_description" class="form-label fw-semibold small">Description / Default Instructions</label>
                                <textarea id="create_description" name="description" rows="2" class="form-control" placeholder="Optional standard notes or guidelines for cleaners performing this task…"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <hr class="my-1">
                                <span class="eyebrow d-block mb-2">Quality & Evidence Requirements</span>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="before_photo_required" value="1" id="create_before_photo">
                                            <label class="form-check-label small" for="create_before_photo">Require Before Photos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="after_photo_required" value="1" id="create_after_photo" checked>
                                            <label class="form-check-label small" for="create_after_photo">Require After Photos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="approval_required" value="1" id="create_approval_required">
                                            <label class="form-check-label small" for="create_approval_required">Supervisor Approval Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="create_min_photos" class="form-label extra-small text-muted mb-1">Minimum Photos Count</label>
                                        <input type="number" min="0" max="50" id="create_min_photos" name="minimum_photo_count" value="1" class="form-control form-control-sm" style="max-width: 140px;">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="form-check form-switch pt-3">
                                            <input class="form-check-input" type="checkbox" name="active" value="1" id="create_active" checked>
                                            <label class="form-check-label small fw-semibold" for="create_active">Active Task Type</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Create Task Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Task Type Modal -->
    <div class="modal fade" id="editTaskTypeModal" tabindex="-1" aria-labelledby="editTaskTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="editTaskTypeForm" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold" id="editTaskTypeModalLabel"><i class="bi bi-pencil-square me-1 text-primary"></i>Edit Task Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="edit_name" class="form-label fw-semibold small">Task Type Name <span class="text-danger">*</span></label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_priority" class="form-label fw-semibold small">Default Priority <span class="text-danger">*</span></label>
                                <select name="default_priority" id="edit_priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_duration" class="form-label fw-semibold small">Est. Duration (Minutes)</label>
                                <input type="number" min="1" max="1440" id="edit_duration" name="default_estimated_duration_minutes" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_checklist" class="form-label fw-semibold small">Default Checklist</label>
                                <select name="default_checklist_id" id="edit_checklist" class="form-select">
                                    <option value="">None (No checklist attached)</option>
                                    @foreach ($checklists as $checklist)
                                        <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label fw-semibold small">Description / Default Instructions</label>
                                <textarea id="edit_description" name="description" rows="2" class="form-control"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <hr class="my-1">
                                <span class="eyebrow d-block mb-2">Quality & Evidence Requirements</span>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="before_photo_required" value="1" id="edit_before_photo">
                                            <label class="form-check-label small" for="edit_before_photo">Require Before Photos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="after_photo_required" value="1" id="edit_after_photo">
                                            <label class="form-check-label small" for="edit_after_photo">Require After Photos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="approval_required" value="1" id="edit_approval_required">
                                            <label class="form-check-label small" for="edit_approval_required">Supervisor Approval Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="edit_min_photos" class="form-label extra-small text-muted mb-1">Minimum Photos Count</label>
                                        <input type="number" min="0" max="50" id="edit_min_photos" name="minimum_photo_count" class="form-control form-control-sm" style="max-width: 140px;">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="form-check form-switch pt-3">
                                            <input class="form-check-input" type="checkbox" name="active" value="1" id="edit_active">
                                            <label class="form-check-label small fw-semibold" for="edit_active">Active Task Type</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function ($) {
        $('.btn-edit-type').on('click', function () {
            var btn = $(this);
            $('#editTaskTypeForm').attr('action', btn.data('update-url'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_description').val(btn.data('description') || '');
            $('#edit_priority').val(btn.data('priority') || 'medium');
            $('#edit_duration').val(btn.data('duration') || '');
            $('#edit_checklist').val(btn.data('checklist') || '');
            $('#edit_before_photo').prop('checked', btn.data('before') == 1);
            $('#edit_after_photo').prop('checked', btn.data('after') == 1);
            $('#edit_approval_required').prop('checked', btn.data('approval') == 1);
            $('#edit_active').prop('checked', btn.data('active') == 1);
            $('#edit_min_photos').val(btn.data('photos') !== undefined ? btn.data('photos') : 0);

            var modal = new bootstrap.Modal(document.getElementById('editTaskTypeModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush

