@extends('layouts.app')

@section('title', 'Linen Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 reveal">
    <div>
        <span class="eyebrow">System · Configuration</span>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h4 mb-0 font-weight-bold">Linen Types</h1>
            <span class="badge bg-light text-muted border mono extra-small">{{ $linenTypes->count() }} types</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('bed-types') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-layout-sidebar me-1"></i>Bed Types
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-bold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#linenModal" id="btn-add-linen">
            <i class="bi bi-plus-lg me-1"></i>New Linen Type
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

<!-- Linen Types Table -->
<div class="card shadow-sm reveal" style="--d: 80ms">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light mono extra-small text-uppercase">
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Name</th>
                    <th>Standard Rate</th>
                    <th>Description</th>
                    <th class="text-center">Usage</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($linenTypes as $type)
                    <tr>
                        <td class="mono extra-small text-muted">#{{ $type->id }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $type->name }}</span>
                        </td>
                        <td>
                            <span class="mono fw-bold text-success">${{ number_format($type->rate, 2) }}</span>
                            <span class="text-muted extra-small">/ unit</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $type->description ?: '—' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border mono extra-small">
                                {{ $type->property_linens_count }} {{ Str::plural('property', $type->property_linens_count) }}
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
                                <button type="button" class="btn btn-outline-secondary btn-xs btn-edit-linen" 
                                    data-linen='@json($type)'
                                    title="Edit Linen Type">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('linen-types.destroy', $type) }}" onsubmit="return confirm('Archive this linen type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="Archive Linen Type">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-tag fs-2 d-block mb-1 opacity-50"></i>
                            No linen types configured. Click <strong>New Linen Type</strong> to add standard items.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="linenModal" tabindex="-1" aria-labelledby="linenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('linen-types.store') }}" id="linen-form">
                @csrf
                <div id="linen-method-container"></div>
                <div class="modal-header py-2 px-3">
                    <h5 class="modal-title h6 font-weight-bold mb-0" id="linenModalLabel">New Linen Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-7">
                            <label for="linen-name" class="form-label extra-small mono text-uppercase text-muted mb-1">Linen Name <span class="text-danger">*</span></label>
                            <input type="text" id="linen-name" name="name" class="form-control form-control-sm" placeholder="e.g. King Sheet Set" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="linen-rate" class="form-label extra-small mono text-uppercase text-muted mb-1">Standard Rate ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" id="linen-rate" name="rate" class="form-control form-control-sm mono fw-bold" placeholder="0.00" required>
                        </div>
                        <div class="col-12">
                            <label for="linen-description" class="form-label extra-small mono text-uppercase text-muted mb-1">Description</label>
                            <input type="text" id="linen-description" name="description" class="form-control form-control-sm" placeholder="e.g. Fitted sheet, flat sheet, 2 pillowcases">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="linen-active" checked>
                                <label class="form-check-label small" for="linen-active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Linen Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var storeUrl = "{{ route('linen-types.store') }}";

        $('#btn-add-linen').on('click', function () {
            $('#linenModalLabel').text('New Linen Type');
            $('#linen-form').attr('action', storeUrl);
            $('#linen-method-container').empty();
            $('#linen-name').val('');
            $('#linen-rate').val('0.00');
            $('#linen-description').val('');
            $('#linen-active').prop('checked', true);
        });

        $(document).on('click', '.btn-edit-linen', function () {
            var linen = $(this).data('linen');
            var updateUrl = "{{ url('admin/linen-types') }}/" + linen.id;

            $('#linenModalLabel').text('Edit Linen Type: ' + linen.name);
            $('#linen-form').attr('action', updateUrl);
            $('#linen-method-container').html('@method("PUT")');

            $('#linen-name').val(linen.name || '');
            $('#linen-rate').val(linen.rate || '0.00');
            $('#linen-description').val(linen.description || '');
            $('#linen-active').prop('checked', !!linen.active);

            var modal = new bootstrap.Modal(document.getElementById('linenModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush
