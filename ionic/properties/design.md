# Properties — Design

## Components

- `PropertyCard` — name, address micro-label, category + tag badges, geocode status chip (missing-coords warning tint).
- `FilterSheet` — reused; properties filter set configured from a single filter-config registry (same registry drives all list screens).
- `ReferenceStore` — shared cached service for `/property-categories` + `/property-tags`; fetched once per session, invalidated on create; cache per project convention (backend may cache too — app doesn't refetch on every picker open).

## State & data

- List: same pattern as personnel (filter combo → query params, page cache, pull-to-refresh).
- Detail: `GET /properties/{id}`; map via Capacitor map plugin or system maps intent with lat/lng (no API key needed for system maps — lazy choice, upgrade to embedded map only if required).
- Create: on 201, invalidate list + reference caches; show geocode-pending state (server async).

## Geocode handling

- `geocode_status` chip on card + detail; "Retry geocode" action when failed (POST retry, refetch detail).

## Testing

- Vitest: filter serialization (bool params like missing_coords/unassigned must serialize correctly), reference cache invalidation.
- Manual: filter parity against web, map open, create + retry-geocode flow both platforms.
