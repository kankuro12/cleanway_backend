# Properties — Suggestions

- **Geocode via queue always**: fast-create never blocks on Google; response returns immediately, geocode job updates `geocode_status` async (spec §7.3.6-7.3.7).
- **Don't re-geocode unchanged**: store hash of name+address; skip job when hash matches and status is resolved/failed (spec §7.3).
- **Radius config cached**: effective-radius fallback read via config cache; per-event snapshot stored at check-in (attendance phase) so history stays stable (spec §2.3).
- **Select only needed columns** in search; index: name, address, google_place_id, category, active, geocode_status, latitude/longitude.
- **Tag merge**: wrap in transaction, repoint pivot rows before deleting duplicate.
- **Fast entry UX**: enter → Google autocomplete fills → save; optional section collapse state persisted.
- **Policy note**: supervisor scoped to assigned properties via `PersonnelScope`; cleaner sees only properties of own tasks.