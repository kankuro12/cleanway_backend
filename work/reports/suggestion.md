# Reports & Hardening — Suggestions

- **One report service**: `app/Domain/Reports/ReportService.php` with filter DTO — web + API + exports share it (spec §0.5).
- **Aggregates over scans**: dashboard counters via indexed grouped queries; avoid per-row models.
- **Export via job**: `export_jobs` rows (type, filters, status, path); user downloads when done; cleanup old files.
- **Settings cache**: `Cache::remember` on `system_settings`/`organization_settings`; `flush` on write (AGENTS.md cached-settings rule).
- **Audit viewer**: paginated + filtered by entity/action/date; never expose `before/after` raw to cleaners.
- **OpenAPI**: generate from routes after stabilization (dedicated command), not hand-maintained.
- **CORS**: restrict to mobile origin(s) only.
- **Pint + CI before hardening phase** — catches style drift early.
- **Backup**: document mysqldump/restore + verify job monthly.