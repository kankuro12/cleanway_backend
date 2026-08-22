{{--
    Compact mobile filter bar (mobile_filters_compact_lists_plan).
    Usage before the page's GET filter <form id="filter-form">:
    @include('partials.compact-filter-bar', ['searchNames' => ['search'], 'searchPlaceholder' => 'Search…'])
    The form must wrap its fields in <div class="row g-2 filter-sheet-body"> and
    carry <div class="filter-sheet-head"> + <div class="filter-sheet-foot"> (d-md-none handled in CSS).
--}}
@props([
    'searchNames' => ['search'],
    'searchPlaceholder' => 'Search…',
    'hideJsPills' => false,
    'hideFilters' => false,
    'hideSearchIcon' => false,
])

@php
    $searchKeys = array_merge(['page'], $searchNames);
    $filterParams = collect(request()->query())
        ->except($searchKeys)
        ->filter(fn ($v) => is_string($v) && $v !== '')
        ->count();
@endphp

<div class="compact-filter-bar d-md-none mb-3 reveal" style="--d: 80ms">
    @if(!empty($searchNames))
        <form method="GET" action="{{ url()->current() }}" class="cf-search" role="search">
            @foreach (request()->except($searchKeys) as $key => $value)
                @if(is_string($value) && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            @unless($hideSearchIcon)
            <i class="bi bi-search" aria-hidden="true"></i>
            @endunless
            <label class="visually-hidden" for="cf-search">{{ $searchPlaceholder }}</label>
            <input type="search" id="cf-search" name="{{ $searchNames[0] }}" value="{{ request($searchNames[0]) }}" class="form-control" placeholder="{{ $searchPlaceholder }}">
        </form>
    @endif
    @unless($hideFilters)
    <button type="button" class="btn btn-touch flex-shrink-0" id="filter-toggle" aria-expanded="false" aria-controls="filter-form">
        <i class="bi bi-sliders me-1" aria-hidden="true"></i>Filters
        @if($filterParams > 0)<span class="badge text-bg-primary ms-1 filter-count">{{ $filterParams }}</span>@endif
    </button>
    @if($filterParams > 0)
        <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-touch flex-shrink-0" aria-label="Clear filters">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </a>
    @endif
    @endunless
</div>
@if(!$hideJsPills)
<div class="filter-pills d-md-none mb-3 d-none" id="filter-pills" aria-label="Active filters"></div>
@endif
