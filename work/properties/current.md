# Properties — Current

## Done

- Nothing (module not started).

## In Progress

- Nothing.

## Next

1. Migrations: `properties`, `property_categories`, `property_tags`, pivot, `property_assignments`, `property_geocode_attempts`.
2. Models + relationships (`Property::category/tags/assignments`, morph `PropertyAssignment`).
3. Fast-create form (name + address + expandable optional section).
4. Google Places service (`app/Services/Geocoding/GooglePlaces.php`) + autocomplete/details endpoints.
5. Geocode job + `RetryPropertyGeocode` action + admin retry button.
6. Categories/tags/assignments CRUD + bulk tag ops + merge/archive.
7. Search service with filters; index migrations.
8. Seeder: sample properties, categories, tags.
9. Tests: fast create, Google-failure fallback, geocode selection, radius fallback, assignments, search.