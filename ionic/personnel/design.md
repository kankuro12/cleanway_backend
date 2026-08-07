# Personnel — Design

## Components

- `PersonnelCard` — avatar initials (navy circle, mono initials), name (Archivo), role + status badges, employee_no micro-label.
- `RoleBadge` / `StatusBadge` — shared badge component; role as tinted badge (0 admin, 1 supervisor, 2 cleaner), status dot + label.

## State & data

- List: filter state → query params on `GET /personnel`; cache per filter combo (LRU, small); pull-to-refresh.
- Detail: `GET /personnel/{id}` on enter; cached per session.
- Team status: fetched on tab enter; light polling (30s) while screen active — matches web's status-rail freshness without hammering the API (server has no push for this).

## Admin CRUD

- Create/update forms as ion-modal; on success invalidate list + detail caches.
- Archive: confirmation alert (destructive styling) — no silent deletes (backend soft-deletes per project rules).

## Testing

- Vitest: filter param serialization, cache invalidation on CRUD, role/status mapping.
- Manual: supervisor browse + team status both platforms; one admin CRUD round-trip.
