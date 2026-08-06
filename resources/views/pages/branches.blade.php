@extends('layouts.app')

@section('title', 'Branches')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People · Structure</span>
            <h2 class="h3 mt-1 mb-0">Branches</h2>
        </div>
        <a href="{{ route('personnel') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to personnel
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm reveal" style="--d: 80ms">
                <div class="card-header"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add branch</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('branches.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control">
                        </div>
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Create branch
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm reveal" style="--d: 140ms">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-cards">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Personnel</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $branch)
                                <tr>
                                    <td data-label="Name" class="fw-semibold text-dark">{{ $branch->name }}</td>
                                    <td data-label="Address">{{ $branch->address ?? '—' }}</td>
                                    <td data-label="Personnel">{{ $branch->users_count }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge status-{{ $branch->active ? 'active' : 'muted' }}">{{ $branch->active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td data-label="Actions" class="text-end">
                                        <form method="POST" action="{{ route('branches.update', $branch) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $branch->name }}">
                                            <input type="hidden" name="address" value="{{ $branch->address }}">
                                            <input type="hidden" name="active" value="{{ $branch->active ? 0 : 1 }}">
                                            <button class="btn btn-sm btn-outline-secondary">{{ $branch->active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
                                            No branches yet.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
