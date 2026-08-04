@php
    $address = $task->address_snapshot ?: ($task->property?->formatted_address ?: $task->property?->address ?: $task->property_name_snapshot ?: '');
    $query = urlencode($address);
    $ua = (string) request()->userAgent();
    // Mobile: open the platform maps app (Apple Maps on iOS, Google Maps elsewhere).
    // Desktop: Google Maps directions in the browser.
    $url = preg_match('/iPhone|iPad|iPod/i', $ua)
        ? "https://maps.apple.com/?daddr={$query}"
        : "https://www.google.com/maps/dir/?api=1&destination={$query}";
@endphp
@if($address)
    <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary {{ $class ?? '' }}" title="Get directions to {{ $address }}">
        <i class="bi bi-sign-turn-right me-1" aria-hidden="true"></i>Directions
    </a>
@endif
