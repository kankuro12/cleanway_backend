# Reports & Hardening — Design

## Data sources

- Dashboards: `users` (status/role), `tasks` (status/schedule), `task_approvals`, `attendance_events`, `gps_exceptions`, `incidents`, `properties` (geocode/assignment state).
- Reports: same + `task_status_histories` (durations), `task_assignments` (performance), `attendance_events` (hours/overtime via AttendanceRules).

## export_jobs

`id, type, filters json, status(pending|processing|done|failed), file_path, requested_by, requested_at, completed_at`

## Settings

```
system_settings:      key, value, description        (radius default, accuracy threshold, policy)
organization_settings: key, value, description       (org radius, notification rules)
```

Cache key: `settings:{type}` — invalidated on write.

## API envelope

```json
{ "data": ..., "meta": { "pagination": ... }, "links": ... }
```

Validation: `{ "message": "...", "errors": { "field": ["..."] } }` (already wired in `bootstrap/app.php` for api/* + JSON expects).

## Key services

- `app/Domain/Reports/ReportService.php` (+ filter DTO)
- `app/Domain/Reports/ExportJob.php` (queued)
- `app/Domain/Settings/SettingsService.php` (cached get/set)
- `app/Http/Resources/` per module
- `app/Support/Envelope.php` (response wrapper)

## Hardening checklist

- [ ] Every web route behind auth + permission.
- [ ] API routes behind Sanctum + permission; rate limited.
- [ ] CORS restricted; uploads validated (mime/size/dimensions/checksum).
- [ ] Query audit: N+1 free, indexes per spec §22.
- [ ] No secrets in code; env template complete.
- [ ] Backup/restore tested; production checklist doc.
- [ ] Full E2E spec §24.4 suite green.