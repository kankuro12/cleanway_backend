# Decisions Pending / Resolved

## Resolved

- **Settings store**: `design.md` proposed `system_settings` + `organization_settings`; both had identical shape so a single `settings` table with a `scope` column (`system|organization`) ships instead. Cache key `settings:{scope}`.
- **Morph map**: property/task assignment tables store short aliases (`user`, `team`, `branch`); `Relation::enforceMorphMap` in `AppServiceProvider` maps them to models.
- **Completion remarks**: treated as optional when the task already has a description (`CompleteTask` requires remarks only when `gps.require_completion_remarks` is on AND the task has no description).
- **Missing-coordinates default policy**: `override` (block check-in until manager approval) per spec §12.2 — change via Settings UI (`gps_missing_coordinates_policy`).
- **Out-of-radius default policy**: `exception` (allow + record exception row).
- **Evidence processing**: `ProcessEvidenceImage` computes checksum/dimensions and marks ready; real compression/thumbnails deferred (storage concerns for later).

## Pending

- Notifications: email + FCM push delivered; SMS channel still a stub (`notification_deliveries` row only). Web service-worker token registration pending (`work/firebase/current.md`).
- GPS exception resolution UI (manager approve/deny) — schema + policy handling exist, UI deferred.
- Periodic location tracking during shifts (spec §12.3) — policy flag needed before implementation.
- CSV import of properties — deferred to future.md.
- Ionic mobile app — explicitly deferred by build plan (web-first).
