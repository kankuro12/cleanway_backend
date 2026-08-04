# Properties — Design

## ER

```
property_categories ─< properties >── property_tags (pivot)
                            │
                            └─< property_assignments >── (morph assignable: user|team|branch)
property_geocode_attempts: property_id FK, query, status, result_json, score, attempted_at
```

- `properties.uuid` unique — API + Ionic reference by uuid.
- `property_assignments` polymorphic `assignable_type/assignable_id` + `assignment_role` (manager|supervisor|cleaner|team|branch|service_area), `start_date`, `end_date`, `is_primary`, `assigned_by`, `reason`.
- Soft deletes on properties, categories, tags.

## Radius fallback (spec §2.3)

1. `properties.permitted_check_in_radius_meters`
2. `property_categories.default_check_in_radius_meters`
3. `organization_settings.default_check_in_radius_meters`
4. system fallback constant (e.g. 150 m)

Effective radius snapshotted onto each GPS/attendance event.

## Key services

- `app/Domain/Properties/CreateProperty.php` — validates name+address only, enqueues geocode if coords missing.
- `app/Domain/Properties/ResolvePropertyCoordinates.php` — Places Details/geocode, score, save.
- `app/Domain/Properties/RetryPropertyGeocode.php` — admin manual retry.
- `app/Domain/Properties/AssignPropertyPersonnel.php` — dated assignment write + history.
- `app/Services/Geocoding/` — GooglePlaces client (backend key, timeout, no key exposure).
- `app/Services/Geocoding/EffectiveRadiusResolver.php`.

## Tests

- Fast create (name+address only), Google down → save + `pending` status.
- Geocode success/failure/manual pin selection.
- Radius fallback chain.
- Assignment history + primary flag + temporary dates.
- Search filter matrix.