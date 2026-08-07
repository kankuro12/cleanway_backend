# Tasks — Design

## Components

- `TaskCard` (shared) — status-tinted left border (same tint mapping as web feed cards), mono micro-labels, 44px action strip slot.
- `FilterSheet` (shared) — bottom sheet + pills; one implementation reused by all list screens.
- `ChecklistView` — renders snapshot items by type (yes/no toggle, pass/fail, text, numeric, photo-required w/ capture); disabled while task not in progress.
- `GpsBar` — shows current accuracy, distance hint (from check-in response), inside/outside geofence state.
- `EvidenceGrid` — thumbnails + type badge; tap → full screen.

## State & data

- Task list: segment state (current/finished) → `GET /me/tasks` w/ status filter; cache last page per segment; pull-to-refresh refetches first page.
- Detail: `GET /tasks/{id}` on enter; after any write action, refetch detail (server is source of truth; the 422-on-illegal-transition message is shown verbatim).
- Checklist responses kept in memory during the flow; submitted via `complete`; not persisted locally (server owns them) except inside the offline outbox (Phase 8).

## GPS integration

- Geolocation service (shared, from foundation) returns lat/lng/accuracy + mock flag; `is_mock_location` from plugin where available.
- Check-in flow: obtain fix → POST → handle 403 blocked (show exception reason, allow "report anyway" only if policy allows; otherwise disable).
- Device id: stable per-install UUID stored in secure storage; sent as `device_id` on all GPS/evidence calls.

## Deep links

- `task/{id}` → detail (used by push notifications).

## Testing

- Vitest: filter param serialization, transition state mapping, checklist completeness pre-check (mirror of backend 422 logic — local fail-fast).
- Manual: full field completion on both platforms + 4.9 filter sheet.
