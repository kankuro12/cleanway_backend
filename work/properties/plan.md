# Properties

Objective: fast property entry (name + address only), Google Places/geocode, categories, tags, dated personnel assignments, efficient search (spec §2.2–2.3, §7).

## Scope

1. **Properties table** — full contract per spec §7.1 (uuid, name NOT NULL, address NOT NULL, formatted_address, google_place_id, latitude/longitude decimal, geocode_status, geocoded_at, location_source, permitted_check_in_radius_meters nullable, category FK, contact fields, postal_code, instructions, service_frequency, active default true, internal_notes, created_by, updated_by, soft deletes).
2. **Fast create** — only name + address required; all else nullable; optional details hidden behind expandable section (spec §17.1).
3. **Google Places** — backend-keyed autocomplete + Place Details endpoints (key never exposed to JS); map preview + manual pin adjustment.
4. **Geocoding** — `ResolvePropertyCoordinates` action + queued job with backoff/retry; score/select best result; geocode attempts table; admin retry action; never re-geocode unchanged addresses; save `geocode_status` (`pending|resolved|manually_adjusted|failed|not_requested`).
5. **Radius fallback** (spec §2.3) — property → category default → org-wide → system fallback; snapshot effective radius per GPS event (attendance phase).
6. **Categories** — `property_categories` (name, slug, description, default radius, default task type/checklist/manager/team, default safety instructions, active, sort order).
7. **Tags** — `property_tags` + pivot (name, slug, description, active, color, sort order); bulk assign/remove; archive; merge duplicates.
8. **Assignments** — `property_assignments` (property_id, assignable_type/id, assignment_role, start/end date, primary flag, assigned_by, reason) — managers, supervisors, cleaners, teams, branches, service areas; permanent + temporary.
9. **Search** — indexed search + combined filters per spec §7.7 (name, address, formatted address, place id, contact, category, tags, assignees, team, branch, active, last/next service, GPS availability, geocode status, missing coords, unassigned).

## Permissions used

`3.1` view, `3.2` create, `3.3` edit, `3.4` categories, `3.5` tags, `3.6` assignments.

## API

`GET /api/v1/properties/{property}`, `GET /api/v1/property-search`, property CRUD behind `3.x` keys.

## Exit criteria (spec §25 Phase 1)

Saves with name+address; coordinates populate when Google succeeds; still saves when Google fails; categories/tags/assignments added later; search + filters work.