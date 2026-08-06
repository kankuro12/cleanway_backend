@extends('layouts.app')

@section('title', 'Properties')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Properties · Registry</span>
            <h2 class="h3 mt-1 mb-0">Property registry</h2>
        </div>
        @if(auth()->user()->hasPermission('3.2'))
            <a href="{{ route('properties.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-building-add me-1" aria-hidden="true"></i>Fast create
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    @include('partials.compact-filter-bar', ['searchNames' => ['search'], 'searchPlaceholder' => 'Search name, address, contact…'])

    <form method="GET" id="filter-form" class="filter-form mb-3 reveal" style="--d: 80ms" role="search">
        <div class="filter-sheet-head">
            <span class="mono text-muted">Filter options</span>
            <button type="button" class="btn btn-icon-touch" data-filter-close aria-label="Close filters">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="row g-2 filter-sheet-body">
        <div class="col-md-3">
            <label for="search" class="visually-hidden">Search</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, address, contact…">
        </div>
        <div class="col-md-2">
            <label for="category_id" class="visually-hidden">Category</label>
            <select name="category_id" id="category_id" class="form-select form-select-sm">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="tag_id" class="visually-hidden">Tag</label>
            <select name="tag_id" id="tag_id" class="form-select form-select-sm">
                <option value="">All tags</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(request('tag_id') == $tag->id)>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="geocode_status" class="visually-hidden">Geocode status</label>
            <select name="geocode_status" id="geocode_status" class="form-select form-select-sm">
                <option value="">All geocode states</option>
                @foreach (['pending', 'resolved', 'manually_adjusted', 'failed', 'not_requested'] as $status)
                    <option value="{{ $status }}" @selected(request('geocode_status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-outline-secondary flex-fill d-none d-md-block">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
            </button>
            <div class="form-check form-check-inline m-0">
                <input class="form-check-input" type="checkbox" id="missing_coords" name="missing_coords" value="1" @checked(request('missing_coords'))>
                <label class="form-check-label small" for="missing_coords">Missing coords</label>
            </div>
            <div class="form-check form-check-inline m-0">
                <input class="form-check-input" type="checkbox" id="unassigned" name="unassigned" value="1" @checked(request('unassigned'))>
                <label class="form-check-label small" for="unassigned">Unassigned</label>
            </div>
        </div>
        </div>
        <div class="filter-sheet-foot">
            <button type="submit" class="btn btn-touch w-100">Apply filters</button>
        </div>
    </form>

    @php
        $q = request()->query();
        $pillUrl = function (array $overrides) use ($q) {
            $merged = array_merge($q, $overrides);
            foreach ($overrides as $k => $v) {
                if ($v === null) unset($merged[$k]);
            }
            return url()->current() . '?' . http_build_query($merged);
        };
    @endphp

    <div class="filter-pills d-lg-none mb-3 reveal" style="--d: 100ms" role="navigation" aria-label="Quick filters">
        <a href="{{ $pillUrl(['category_id' => null, 'missing_coords' => null, 'unassigned' => null]) }}" class="pill @if(!request()->filled('category_id') && !request('missing_coords') && !request('unassigned')) active @endif">All</a>
        <a href="{{ $pillUrl(['category_id' => null, 'missing_coords' => 1, 'unassigned' => null]) }}" class="pill @if(!request()->filled('category_id') && request('missing_coords')) active @endif">Missing coords</a>
        <a href="{{ $pillUrl(['category_id' => null, 'missing_coords' => null, 'unassigned' => 1]) }}" class="pill @if(!request()->filled('category_id') && request('unassigned')) active @endif">Unassigned</a>
        @foreach ($categories->take(3) as $category)
            <a href="{{ $pillUrl(['category_id' => $category->id, 'missing_coords' => null, 'unassigned' => null]) }}" class="pill @if(request('category_id') == $category->id) active @endif">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="d-lg-none p-3 d-flex flex-column gap-2">
            @forelse ($properties as $property)
                <div class="mobile-task-card compact">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                        <span class="mtc-title">{{ $property->name }}</span>
                        <span class="status-badge status-{{ $property->active ? 'active' : 'muted' }}">{{ $property->active ? 'active' : 'inactive' }}</span>
                    </div>
                    <div class="mtc-meta mb-1">{{ $property->address }}</div>
                    <div class="mtc-meta mb-2">
                        {{ $property->category?->name ?? '—' }}
                        @forelse ($property->tags as $tag)
                            <span class="status-badge status-muted ms-1" @if($tag->color) style="--dot: {{ $tag->color }}" @endif>{{ $tag->name }}</span>
                        @empty
                        @endforelse
                        · <span class="status-badge status-{{ $property->geocode_status === 'resolved' ? 'active' : ($property->geocode_status === 'manually_adjusted' ? 'warning' : ($property->geocode_status === 'failed' ? 'danger' : 'muted')) }}">
                            {{ str_replace('_', ' ', $property->geocode_status) }}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        @if($property->latitude && $property->longitude)
                            <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-icon-touch" aria-label="Open {{ $property->name }} in maps">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            </a>
                        @endif
                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-secondary btn-touch flex-fill">
                            <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                    No properties match the current filters.
                </div>
            @endforelse
        </div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Category</th>
                        <th>Tags</th>
                        <th>GPS</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($properties as $property)
                        <tr>
                            <td data-label="Property">
                                <span class="fw-semibold text-dark">{{ $property->name }}</span><br>
                                <small class="text-muted">{{ $property->address }}</small>
                            </td>
                            <td data-label="Category">{{ $property->category?->name ?? '—' }}</td>
                            <td data-label="Tags">
                                @forelse ($property->tags as $tag)
                                    <span class="status-badge status-muted" @if($tag->color) style="--dot: {{ $tag->color }}" @endif>{{ $tag->name }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td data-label="GPS">
                                <span class="status-badge status-{{ $property->geocode_status === 'resolved' ? 'active' : ($property->geocode_status === 'manually_adjusted' ? 'warning' : ($property->geocode_status === 'failed' ? 'danger' : 'muted')) }}">
                                    {{ str_replace('_', ' ', $property->geocode_status) }}
                                </span>
                                @if($property->latitude && $property->longitude)
                                    <br><small class="text-muted">{{ round($property->latitude, 4) }}, {{ round($property->longitude, 4) }}</small>
                                @endif
                            </td>
                            <td data-label="Active">
                                <span class="status-badge status-{{ $property->active ? 'active' : 'muted' }}">{{ $property->active ? 'active' : 'inactive' }}</span>
                            </td>
                            <td class="text-end" data-label="Actions">
                                <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                                    No properties match the current filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $properties->links() }}</div>
@endsection
