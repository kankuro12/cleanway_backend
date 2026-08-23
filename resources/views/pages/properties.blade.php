@extends('layouts.app')

@section('title', 'Properties')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Properties · Registry</span>
            <h2 class="h3 mt-1 mb-0">Property registry</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(auth()->user()->hasPermission('3.1'))
                <a href="{{ route('properties.mass-manage') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-table me-1" aria-hidden="true"></i>Mass Manage
                </a>
            @endif
            @if(auth()->user()->hasPermission('3.2'))
                <a href="{{ route('properties.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                    <i class="bi bi-building-add me-1" aria-hidden="true"></i>Fast create
                </a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    @include('partials.compact-filter-bar', ['searchNames' => ['search'], 'searchPlaceholder' => 'Search name, address, contact…', 'hideJsPills' => true, 'hideFilters' => true, 'hideSearchIcon' => true])

    <form method="GET" action="{{ url()->current() }}" class="d-none d-md-block mb-3 reveal" style="--d: 80ms" role="search">
        <label class="visually-hidden" for="search-desktop">Search</label>
        <input type="search" id="search-desktop" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, address, contact…">
    </form>

    <div class="card shadow-sm reveal" style="--d: 140ms">
        <div class="d-lg-none p-2 d-flex flex-column gap-2">
            @forelse ($properties as $property)
                <div class="mobile-task-card compact">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                        <span class="mtc-title text-truncate">{{ $property->name }}</span>
                        <span class="status-badge status-{{ $property->active ? 'active' : 'muted' }}">{{ $property->active ? 'active' : 'inactive' }}</span>
                    </div>
                    <div class="mtc-meta text-truncate mb-1"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ $property->address }}</div>
                    @if($property->client)
                        <div class="mtc-meta text-truncate mb-1">
                            <span class="badge bg-light text-dark border mono extra-small"><i class="bi bi-person me-1 text-muted"></i>{{ $property->client->name }}</span>
                        </div>
                    @endif
                    <div class="mtc-meta mb-2">
                        {{ $property->category?->name ?? '—' }}
                        @if($property->bedrooms_count || $property->bathrooms_count)
                            · <span class="mono extra-small text-muted">{{ $property->bedrooms_count }} bed · {{ $property->bathrooms_count }} bath</span>
                        @endif
                        @foreach ($property->tags as $tag)
                            <span class="status-badge status-muted ms-1" @if($tag->color) style="--dot: {{ $tag->color }}" @endif>{{ $tag->name }}</span>
                        @endforeach
                        · <span class="status-badge status-{{ $property->geocode_status === 'resolved' ? 'active' : ($property->geocode_status === 'manually_adjusted' ? 'warning' : ($property->geocode_status === 'failed' ? 'danger' : 'muted')) }}">
                            {{ str_replace('_', ' ', $property->geocode_status) }}
                        </span>
                    </div>
                    <div class="d-flex gap-1 mt-1">
                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-secondary btn-sm flex-fill py-1 px-2">
                            <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit
                        </a>
                        @if($property->latitude && $property->longitude)
                            <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm flex-fill py-1 px-2" aria-label="Open {{ $property->name }} in maps">
                                <i class="bi bi-sign-turn-right me-1" aria-hidden="true"></i>Directions
                            </a>
                        @endif
                        <a href="{{ route('tasks', ['property_id' => $property->id]) }}" class="btn btn-outline-secondary btn-sm flex-fill py-1 px-2">
                            <i class="bi bi-list-check me-1" aria-hidden="true"></i>Tasks
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                    No properties found.
                </div>
            @endforelse
        </div>
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Client</th>
                        <th>Category / Specs</th>
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
                            <td data-label="Client">
                                @if($property->client)
                                    <a href="{{ route('clients', ['search' => $property->client->name]) }}" class="text-decoration-none fw-semibold small text-dark">
                                        <i class="bi bi-person me-1 text-muted"></i>{{ $property->client->name }}
                                    </a>
                                    @if($property->client->company_name)
                                        <div class="extra-small text-muted mono">{{ $property->client->company_name }}</div>
                                    @endif
                                @else
                                    <span class="text-muted extra-small mono">—</span>
                                @endif
                            </td>
                            <td data-label="Category / Specs">
                                <div>{{ $property->category?->name ?? '—' }}</div>
                                @if($property->bedrooms_count || $property->bathrooms_count)
                                    <div class="extra-small text-muted mono">{{ $property->bedrooms_count }} bed · {{ $property->bathrooms_count }} bath</div>
                                @endif
                            </td>
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
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                        <i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit
                                    </a>
                                    @if($property->latitude && $property->longitude)
                                        <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0 px-2" aria-label="Directions">
                                            <i class="bi bi-sign-turn-right me-1" aria-hidden="true"></i>Directions
                                        </a>
                                    @endif
                                    <a href="{{ route('tasks', ['property_id' => $property->id]) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                        <i class="bi bi-list-check me-1" aria-hidden="true"></i>Tasks
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
                                    No properties found.
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
