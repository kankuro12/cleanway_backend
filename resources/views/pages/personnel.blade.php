@extends('layouts.app')

@section('title', 'Personnel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People</span>
            <h2 class="h3 mt-1 mb-0">Personnel registry</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('teams') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-workspace me-1" aria-hidden="true"></i>Teams
            </a>
            <a href="{{ route('branches') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-diagram-3 me-1" aria-hidden="true"></i>Branches
            </a>
            <a href="{{ route('personnel.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>New person
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3 reveal" style="--d: 80ms" role="search">
        <div class="col-md-4">
            <label for="search" class="visually-hidden">Search</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, email, employee no.">
        </div>
        <div class="col-md-2">
            <label for="role" class="visually-hidden">Role</label>
            <select name="role" id="role" class="form-select form-select-sm">
                <option value="">All roles</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') !== null && (int) request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="status" class="visually-hidden">Status</label>
            <select name="status" id="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                @foreach (['invited', 'active', 'inactive', 'suspended', 'on_leave', 'archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
        </div>
    </form>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Person</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Branch</th>
                        <th>Team</th>
                        <th>Manager</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark">{{ $user->name }}</span><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>
                                <span class="status-badge status-muted">{{ $roles[$user->role] }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ in_array($user->status, ['active']) ? 'active' : (in_array($user->status, ['suspended']) ? 'danger' : 'muted') }}">{{ $user->status }}</span>
                            </td>
                            <td>{{ $user->branch?->name ?? '—' }}</td>
                            <td>{{ $user->team?->name ?? '—' }}</td>
                            <td>{{ $user->manager?->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('personnel.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                                    No personnel match the current filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $users->links() }}</div>
@endsection
