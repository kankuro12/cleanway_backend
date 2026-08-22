@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 reveal">
    <div>
        <span class="eyebrow">Properties · CRM</span>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h4 mb-0 font-weight-bold">Clients</h1>
            <span class="badge bg-light text-muted border mono extra-small">{{ method_exists($clients, 'total') ? $clients->total() : count($clients) }} total</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-house-door me-1"></i>Properties
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-bold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#clientModal" id="btn-add-client">
            <i class="bi bi-plus-lg me-1"></i>New Client
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

<!-- Search and Filter Bar -->
<div class="card shadow-sm mb-3 reveal" style="--d: 60ms">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('clients') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, company, email, phone…">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active Only</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive Only</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                @if($search || $status)
                    <a href="{{ route('clients') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Clients Table -->
<div class="card shadow-sm reveal" style="--d: 100ms">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light mono extra-small text-uppercase">
                <tr>
                    <th>Client / Contact</th>
                    <th>Company</th>
                    <th>Email / Phone</th>
                    <th class="text-center">Properties</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $client->name }}</div>
                            @if($client->address)
                                <div class="text-muted extra-small text-truncate" style="max-width: 250px;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $client->address }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($client->company_name)
                                <span class="fw-semibold text-dark">{{ $client->company_name }}</span>
                            @else
                                <span class="text-muted extra-small mono">—</span>
                            @endif
                        </td>
                        <td>
                            @if($client->email)
                                <div><a href="mailto:{{ $client->email }}" class="text-decoration-none small"><i class="bi bi-envelope me-1"></i>{{ $client->email }}</a></div>
                            @endif
                            @if($client->phone)
                                <div class="extra-small text-muted mono"><i class="bi bi-telephone me-1"></i>{{ $client->phone }}</div>
                            @endif
                            @if(!$client->email && !$client->phone)
                                <span class="text-muted extra-small mono">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($client->properties_count > 0)
                                <a href="{{ route('properties', ['client_id' => $client->id]) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mono extra-small text-decoration-none">
                                    {{ $client->properties_count }} {{ Str::plural('prop', $client->properties_count) }}
                                </a>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill mono extra-small">0 props</span>
                            @endif
                        </td>
                        <td>
                            @if($client->active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mono extra-small">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill mono extra-small">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-xs btn-edit-client" 
                                    data-client='@json($client)'
                                    title="Edit Client">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Archive this client?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="Archive Client">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-1 opacity-50"></i>
                            No clients found. Click <strong>New Client</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (method_exists($clients, 'hasPages') && $clients->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $clients->links() }}
        </div>
    @endif
</div>

<!-- Add / Edit Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('clients.store') }}" id="client-form">
                @csrf
                <div id="method-container"></div>
                <div class="modal-header py-2 px-3">
                    <h5 class="modal-title h6 font-weight-bold mb-0" id="clientModalLabel">New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="modal-name" class="form-label extra-small mono text-uppercase text-muted mb-1">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" id="modal-name" name="name" class="form-control form-control-sm" placeholder="e.g. Sarah Jenkins" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="modal-company_name" class="form-label extra-small mono text-uppercase text-muted mb-1">Company / Organization</label>
                            <input type="text" id="modal-company_name" name="company_name" class="form-control form-control-sm" placeholder="e.g. Apex Property Management">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="modal-email" class="form-label extra-small mono text-uppercase text-muted mb-1">Email Address</label>
                            <input type="email" id="modal-email" name="email" class="form-control form-control-sm" placeholder="sarah@example.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="modal-phone" class="form-label extra-small mono text-uppercase text-muted mb-1">Phone Number</label>
                            <input type="text" id="modal-phone" name="phone" class="form-control form-control-sm" placeholder="+64 21 000 0000">
                        </div>
                        <div class="col-12">
                            <label for="modal-address" class="form-label extra-small mono text-uppercase text-muted mb-1">Physical / Postal Address</label>
                            <input type="text" id="modal-address" name="address" class="form-control form-control-sm" placeholder="Street, suburb, city…">
                        </div>
                        <div class="col-12">
                            <label for="modal-notes" class="form-label extra-small mono text-uppercase text-muted mb-1">Internal Notes</label>
                            <textarea id="modal-notes" name="notes" rows="2" class="form-control form-control-sm" placeholder="Billing specifics, key holder info…"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="modal-active" checked>
                                <label class="form-check-label small" for="modal-active">Client Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3" id="btn-save-client">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var storeUrl = "{{ route('clients.store') }}";

        $('#btn-add-client').on('click', function () {
            $('#clientModalLabel').text('New Client');
            $('#client-form').attr('action', storeUrl);
            $('#method-container').empty();
            $('#modal-name').val('');
            $('#modal-company_name').val('');
            $('#modal-email').val('');
            $('#modal-phone').val('');
            $('#modal-address').val('');
            $('#modal-notes').val('');
            $('#modal-active').prop('checked', true);
        });

        $(document).on('click', '.btn-edit-client', function () {
            var client = $(this).data('client');
            var updateUrl = "{{ url('admin/clients') }}/" + client.id;

            $('#clientModalLabel').text('Edit Client: ' + client.name);
            $('#client-form').attr('action', updateUrl);
            $('#method-container').html('@method("PUT")');

            $('#modal-name').val(client.name || '');
            $('#modal-company_name').val(client.company_name || '');
            $('#modal-email').val(client.email || '');
            $('#modal-phone').val(client.phone || '');
            $('#modal-address').val(client.address || '');
            $('#modal-notes').val(client.notes || '');
            $('#modal-active').prop('checked', !!client.active);

            var modal = new bootstrap.Modal(document.getElementById('clientModal'));
            modal.show();
        });
    })(jQuery);
</script>
@endpush
