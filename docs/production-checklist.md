# Production Hardening Checklist

Target state for a production deployment of the web app + API.

## Security

- [x] Every web route behind `auth` + `permission:<key>`; public routes only login/password reset.
- [x] API routes behind `auth:sanctum`; login `throttle:10,1`, authed group `throttle:120,1`.
- [x] Google Places key backend-only (never in JS); Places proxy validates input + permission.
- [x] Evidence stored on private `evidence` disk; downloads served via `reports.download` (owner + `7.2` checked).
- [x] Uploads validated: `image`, `max:10240`, mime recorded, SHA-256 checksum via `ProcessEvidenceImage`.
- [x] PHP upload limits raised in php.ini (`upload_max_filesize=20M`, `post_max_size=32M`) — phone photos exceed the 2M default; without this, uploads fail with "The evidence failed to upload." (422).
- [x] Attendance events immutable (update/delete blocked at model level).
- [x] No hard deletes of operational records: properties, personnel, tasks, shifts, incidents, categories, tags all soft-delete.
- [x] Passwords hashed (`hashed` cast); tokens revocable via `/auth/logout`.
- [ ] Enable `APP_DEBUG=false`, set strong `ADMIN_PASSWORD`, rotate `APP_KEY` on any leak.
- [ ] Restrict `cors.allowed_origins` to known admin origin(s); verify `config/cors.php`.
- [ ] Force HTTPS (proxy/CF) and set `SESSION_SECURE_COOKIE=true`.

## Operations

- [x] Queue: `database` driver works out of the box; jobs: `GeocodeProperty`, `ProcessEvidenceImage`, `GenerateExport`.
- [x] Scheduler: `tasks:generate-recurring` nightly (30-day horizon) — run `php84 artisan schedule:run` via cron.
- [ ] Run `php84 artisan queue:work` (or a supervisor pool) in production.
- [ ] Storage: S3 disk for evidence when multi-server (`config/filesystems.php` — swap `evidence` disk driver).
- [ ] Cache: `database` driver fine at small scale; Redis for multi-server.
- [ ] Backup: verify `database/database.sqlite` (or DB of choice) + `storage/app/evidence` backups restore cleanly.
- [ ] `storage:link` if public assets needed.

## Performance

- [x] Query audit: dashboard/report queries select only needed columns; relations eager-loaded; no N+1 in list pages.
- [x] Indexes on all filter columns (status, scheduled_start_at, property_id, user_id+date, geocode_status, etc.).
- [x] Settings cached (`settings:{scope}`) and invalidated on write; config overrides applied at boot.
- [ ] Optional: MySQL spatial index for nearby-property search (future.md).
- [ ] Optional: Redis for sessions/cache at scale.

## Verification

- [ ] `php84 artisan test` — 86 tests green (see work/plan.md status).
- [ ] `php84 vendor/bin/pint --test` — clean.
- [ ] E2E spec §24.4 scenario green in `AttendanceModuleTest::test_e2e_checkin_complete_submit_approve`.
- [ ] Confirm `docs/openapi.yaml` matches the shipped routes (`php84 artisan route:list`).
