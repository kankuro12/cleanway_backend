@extends('layouts.app')

@section('title', 'Bed Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 reveal">
    <div>
        <span class="eyebrow">System · Configuration</span>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h4 mb-0 font-weight-bold">Bed Types</h1>
            <span class="badge bg-light text-muted border mono extra-small">{{ $bedTypes->count() }} types</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('linen-types') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-tag me-1"></i>Linen Types
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-bold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#bedModal" id="btn-add-bed">
            <i class="bi bi-plus-lg me-1"></i>New Bed Type
        </button>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show py-2 reveal" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 reveal" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-1"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Bed Types Table -->
<div class="card shadow-sm reveal" style="--d: 80ms">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light mono extra-small text-uppercase">
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-center">Usage</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bedTypes as $type)
                    <tr>
                        <td class="mono extra-small text-muted">#{{ $type->id }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $type->name }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $type->description ?: '—' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border mono extra-small">
                                {{ $type->property_beds_count }} {{ Str::plural('property', $type->property_beds_count) }}
                            </span>
                        </td>
                        <td>
                            @if($type->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mono extra-small">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill mono extra-small">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-xs btn-edit-bed" 
                                    data-bed='@json($type)'
                                    title="Edit Bed Type">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('bed-types.destroy', $type) }}" onsubmit="return confirm('Archive this bed type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="Archive Bed Type">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-layout-sidebar fs-2 d-block mb-1 opacity-50"></i>
                            No bed types configured. Click <strong>New Bed Type</strong> to add standard beds (King, Queen, Single, etc.).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="bedModal" tabindex="-1" aria-labelledby="bedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('bed-types.store') }}" id="bed-form">
                @csrf
                <div id="bed-method-container"></div>
                <div class="modal-header py-2 px-3">
                    <h5 class="modal-title h6 font-weight-bold mb-0" id="bedModalLabel">New Bed Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <label for="bed-name" class="form-label extra-small mono text-uppercase text-muted mb-1">Bed Type Name <span class="text-danger">*</span></label>
                            <input type="text" id="bed-name" name="name" class="form-control form-control-sm" placeholder="e.g. King, Queen, Single, Bunk Bed" required>
                        </div>
                        <div class="col-12">
                            <label for="bed-description" class="form-label extra-small mono text-uppercase text-muted mb-1">Description</label>
                            <input type="text" id="bed-description" name="description" class="form-control form-control-sm" placeholder="e.g. Standard 180x200cm mattress">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="bed-active" checked>
                                <label class="form-check-label small" for="bed-active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Bed Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var storeUrl = "{{ route('bed-types.store') }}";

        $('#btn-add-bed').on('click', function () {
            $('#bedModalLabel').text('New Bed Type');
            $('#bed-form').attr('action', storeUrl);
            $('#bed-method-container').empty();
            $('#bed-name').val('');
            $('#bed-description').val('');
            $('#bed-active').prop('checked', true);
        });

        $(document).on('click', '.btn-edit-bed', function () {
            var bed = $(this).data('bed');
            var updateUrl = "{{ url('admin/bed-types') }}/" + bed.id;

            $('#bedModalLabel').text('Edit Bed Type: ' + bed.name);
            $('#bed-form').attr('action', updateUrl);
            $('#bed-method-container').html('@method("PUT")');

            $('#bed-name').val(bed.name || '');
            $('#bed-description').val(bed.description || '');
            $('#bed-active').prop('checked', !!bed.active);

            var modal = new bootstrap.Modal(document.getElementById('bedModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush
