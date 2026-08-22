# Properties — Current

## Done

- [x] Migrations: `property_categories`, `property_tags`, `property_tag` pivot, `properties` (full spec §7.1 contract + `geocode_hash`), `property_assignments` (morph), `property_geocode_attempts`.
- [x] Models: `Property` (status/source constants, address-hash, search/filter scopes), `PropertyCategory`, `PropertyTag` (+ `uniqueSlug`), `PropertyAssignment` (morph), `PropertyGeocodeAttempt`.
- [x] Fast create: name + address only required; optional section collapsible; enqueues geocode when coords missing.
- [x] Google Places backend proxy (`PlacesController` + `app/Services/Geocoding/GooglePlaces.php`) — key server-side only; autocomplete + Place Details endpoints; Leaflet map preview with draggable pin on create/edit; `services.google_places.*` config.
- [x] Geocoding: `ResolvePropertyCoordinates` action (Place Details first, geocode fallback, scoring), `GeocodeProperty` job (tries 3, backoff 30s), unchanged-address skip via `geocode_hash`, `RetryPropertyGeocode` + admin retry button, `manually_adjusted` manual pin path, `property_geocode_attempts` log.
- [x] Radius fallback: `EffectiveRadiusResolver` (property → category → `config/organization` → 150 m system fallback).
- [x] Categories CRUD (web, `3.4`).
- [x] Tags: CRUD + bulk assign/remove + merge (transactional pivot repoint) (`3.5`).
- [x] Assignments: `AssignPropertyPersonnel` action (primary-flag demotion, dated, audit), web add/remove (`3.6`).
- [x] Web UI: properties list w/ filters (search, category, tag, geocode status, missing coords, unassigned), fast-create form, edit page (details + geocode + assignments).
- [x] API `/api/v1`: properties list/search/show/crud + retry-geocode + categories/tags lists behind `3.x`, `PropertyResource`, pagination envelope.
- [x] Seeder: `PropertiesSeeder` (2 categories, 3 tags, 3 sample properties, assignments).
- [x] `config/organization.php` + `.env.example` entries.
- [x] Tests: 16 property tests (fast create, validation, permissions, geocode success/failure/offline, no-regeocode, manual pin, radius chain, assignments, search matrix, tag merge, API, archive).
- [x] Reimagined: property is name + address + Leaflet pin (drag or Nominatim geocode from address) + needs_parking flag; requirements removed from property.
- [x] Checklists own requirements: `checklist_items.is_photo_required / is_comment_required`; checklist → subtasks on task create (`CreateTask` creates TaskSubtask + snapshot per item); checklist preview on task create (`GET /admin/checklists/{id}/items`).
- [x] Property forms: Leaflet OSM + Nominatim fallback, geocode button, my-location, draggable pin, needs_parking toggle, mobile responsive (stacked cols, touch map).
- [x] Cleaning & Billing on properties: cleaning_duration_minutes, client_fixed_amount, cleaner_pay_type (fixed/per_hour), cleaner_fixed_amount, cleaner_rate_per_hour, parking_fee, needs_parking.
- [x] `PropertyResource` now exposes needs_parking + coordinates; API no longer accepts requirements_json.
- [x] Migration `2026_08_22_reimagine_property_checklist` drops property_requirements, adds needs_parking + checklist photo/comment flags.

## Verified

- 56 tests green (40 prior + 16 new), pint clean.
- Fast create with name+address only saves; geocode job queued; Google down still saves (status `failed`/`pending`).
- Cleaner blocked from create (403); supervisor can view/create/edit/assign.

## Next

1. Move to tasks module — one-time task locations and service-frequency scheduling reference properties.
2. Bulk tag ops UI from list page (actions exist server-side) — deferred, low priority.
3. Map view / nearby search (spatial index) — deferred to `future.md`.
