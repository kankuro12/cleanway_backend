# Properties

Objective: supervisor/admin property directory — search, filters, detail w/ map; quick-create minimal. Mirrors web properties list (missing-coords / unassigned / category filter pills parity).

## Screens & structures

1. **Properties list** (3.1) — cards: name, address, category badge, tags, assignment state, geocode status chip (missing-coords flagged); filter sheet (search, active, category_id, tag_id, geocode_status, missing_coords, unassigned, assigned_to) + pills; pull-to-refresh + infinite scroll.
2. **Property detail** (3.1) — full fields, categories/tags, assignment (person/team), lat/lng, map (tap → native maps app or embedded map), geocode status; tasks link (deep link to all-tasks filtered by property_id — 4.9).
3. **Quick create** (3.2, minimal) — name + address + category; geocode runs server-side (retry endpoint exists for failures).
4. **Reference lists** — category + tag pickers fed from `/property-categories` / `/property-tags` (cached aggressively — reference data).

## APIs used

`GET /properties` (full filter set), `GET /properties/search`, `GET /properties/{id}`, `GET /property-categories`, `GET /property-tags`, `POST /properties` (3.2), `POST /properties/{id}/retry-geocode` (3.3) — admin update/delete web-only.

## Forms

| Form | Fields | Notes |
|---|---|---|
| Filter sheet | search, active, category_id, tag_id, geocode_status, missing_coords, unassigned, assigned_to | → query params |
| Quick create (3.2) | name, address, category_id (optional), tags (optional) | geocode async server-side |

## Exit criteria

Supervisor browses w/ all web-parity filters, opens detail w/ map, creates a property, retries geocode on failure.
