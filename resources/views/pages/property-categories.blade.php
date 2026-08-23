@extends('layouts.app')

@section('title', 'Property Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Properties · Config</span>
            <h1 class="h3 mt-1 mb-0">Property categories</h1>
        </div>
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Registry
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header mono">New category</div>
        <div class="card-body">
            <form method="POST" action="{{ route('property-categories.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label for="name" class="form-label visually-hidden">Name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="Name" required>
                </div>
                <div class="col-md-3">
                    <label for="default_check_in_radius_meters" class="form-label visually-hidden">Default radius</label>
                    <input type="number" min="0" max="100000" id="default_check_in_radius_meters" name="default_check_in_radius_meters" class="form-control form-control-sm" placeholder="Default radius (m)">
                </div>
                <div class="col-md-3">
                    <label for="default_manager_id" class="form-label visually-hidden">Default manager</label>
                    <select name="default_manager_id" id="default_manager_id" class="form-select form-select-sm">
                        <option value="">Default manager</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="default_team_id" class="form-label visually-hidden">Default team</label>
                    <select name="default_team_id" id="default_team_id" class="form-select form-select-sm">
                        <option value="">Default team</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9">
                    <label for="description" class="form-label visually-hidden">Description</label>
                    <input type="text" id="description" name="description" class="form-control form-control-sm" placeholder="Description (optional)">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus me-1" aria-hidden="true"></i>Add category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Default radius</th>
                        <th>Default manager</th>
                        <th>Default team</th>
                        <th>Properties</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('property-categories.update', $category) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" style="max-width: 220px" required>
                                    <input type="number" min="0" max="100000" name="default_check_in_radius_meters" value="{{ $category->default_check_in_radius_meters }}" class="form-control form-control-sm" style="max-width: 120px" placeholder="Radius m">
                                    <select name="default_manager_id" class="form-select form-select-sm" style="max-width: 150px">
                                        <option value="">Manager</option>
                                        @foreach ($managers as $manager)
                                            <option value="{{ $manager->id }}" @selected($category->default_manager_id === $manager->id)>{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="default_team_id" class="form-select form-select-sm" style="max-width: 150px">
                                        <option value="">Team</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}" @selected($category->default_team_id === $team->id)>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-check d-flex align-items-center m-0">
                                        <input class="form-check-input" type="checkbox" name="active" value="1" id="cat-active-{{ $category->id }}" @checked($category->active)>
                                        <label class="form-check-label small ms-1" for="cat-active-{{ $category->id }}">Active</label>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" title="Save">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $category->default_check_in_radius_meters ? 'active' : 'muted' }}">
                                    {{ $category->default_check_in_radius_meters ? $category->default_check_in_radius_meters.' m' : 'fallback' }}
                                </span>
                            </td>
                            <td>{{ $managers->firstWhere('id', $category->default_manager_id)?->name ?? '—' }}</td>
                            <td>{{ $teams->firstWhere('id', $category->default_team_id)?->name ?? '—' }}</td>
                            <td>{{ $category->properties_count }}</td>
                            <td><span class="status-badge status-{{ $category->active ? 'active' : 'muted' }}">{{ $category->active ? 'active' : 'inactive' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-tags"></i></span>
                                    No categories yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $categories->links() }}</div>
@endsection
