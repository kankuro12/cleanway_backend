# Reports & Hardening

Objective: role-scoped dashboards, reports + exports, audit viewer, settings, API stabilization, performance/security hardening (spec §16, §17.22–24, §21).

## Scope

1. **Dashboards** (spec §16)
   - Admin: active personnel, managers, cleaners, tasks today/active/overdue, pending approvals, late/absent, GPS exceptions, open incidents, properties w/o coords/assignments, geocode failures.
   - Supervisor: scoped team attendance, active tasks, awaiting approval, late/absent, overdue, incidents, location exceptions, upcoming work.
   - Cleaner: current shift/task, next task, check-in action, pending uploads, correction requests, notifications, recent attendance.
2. **Reports** (spec §16.4) — attendance, work hours, overtime, missed shifts, task completion/duration/approval, rejected/reopened, property service history, task type performance, cleaner/manager performance, GPS exceptions, category/tag distribution, unassigned/no-coordinate properties, incidents.
   - Filters: date range, employee, manager, team, branch, property, category, tags, task type, status, priority.
   - Exports: CSV/Excel/PDF via queued `export_jobs`; large exports in background jobs.
3. **Audit viewer** — searchable audit log UI (`9.1`).
4. **Settings** — `system_settings`, `organization_settings` (radius default, accuracy threshold, notification rules, geocode policy); cached aggressively, invalidated on write (`1.4`).
5. **API stabilization** — consistent envelopes `{data,meta,links}`, validation error shape, API Resources, pagination/filtering, rate limits, Sanctum tokens + revocation, OpenAPI spec.
6. **Hardening** — security review (role/policy checks, CORS, upload validation, signed URLs), performance (query audit, indexes, cache), backup verification, production checklist.

## Permissions used

`7.1` view reports, `7.2` export, `9.1` audit view, `1.4` organization settings, `1.1` user settings admin.

## Exit criteria (spec §25 Phase 4)

Web MVP accepted; API contracts stable; OpenAPI complete.