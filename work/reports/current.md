# Reports & Hardening — Current

## Done

- [x] `DashboardWidgets` service: admin (8 stats + attention + today), supervisor (scoped team), cleaner (today/next/unread + corrections) — minimal columns, no N+1.
- [x] Dashboard page rebuilt on real widgets (replaces hardcoded stub).
- [x] `ReportService`: attendance / tasks / approvals / properties / incidents reports with whitelisted filters + headers; used by both web and exports.
- [x] Queued exports: `export_jobs` table, `GenerateExport` job (CSV → `evidence` disk), web queue/status/download with owner + `7.2` gate.
- [x] Settings: single `settings` table (`scope` system|organization), `SettingsService` (cached, invalidated on write), runtime config override at boot (`applyToConfig`), Settings admin UI (`1.4`), `SettingsSeeder` (7 defaults).
- [x] Audit viewer: searchable log UI (`9.1`) with before/after diff expansion.
- [x] API stabilization: consistent `{data, meta, links}` envelopes (personnel/properties/tasks/attendance/notifications), validation error shape wired in bootstrap, `throttle:10,1` on login + `throttle:120,1` on authed group, Sanctum token issuance + revocation, `docs/openapi.yaml`.
- [x] Hardening: private `evidence` disk, upload validation (image/size/mime/checksum), immutable attendance events, soft-delete policy, route-permission audit, `docs/production-checklist.md`, `docs/decisions-pending.md`.
- [x] Tests: 7 (dashboard widgets per role, reports rows, export job + CSV, download ownership, settings cache/write/admin, audit gate) + 1 contract test updated (reports now strict `7.1`).

## Verified

- 87 tests green (86 + view smoke), pint clean.
- `ViewSmokeTest` renders every Blade page (caught + fixed 2 real blade compile bugs: nested `@forelse`, inline `@endif`).
- `php84 artisan route:list` — all protected routes carry permission middleware; public routes: login/password-reset only.
- E2E spec §24.4 scenario green (`AttendanceModuleTest::test_e2e_checkin_complete_submit_approve`).

## Next (deferred)

1. Ionic mobile app — out of scope for web-first delivery (`future.md`).
2. Excel/PDF export formats on top of the CSV pipeline.
3. Saved filters / bulk ops / map view — `future.md` per module.
