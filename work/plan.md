# Cleaning Workforce Platform — Build Plan (Root)

Laravel web + versioned API only. **Ionic deferred** (out of scope for now, see `future.md`).

Operating contract: `../AGENTS.md` (runtime notes + conventions). Full authority: `../specification.md`.

## Delivery Order

1. **Foundation** — auth, RBAC (done), audit, queue, scheduler, storage, design tokens, CI.
2. **Personnel & Teams** — users, branches, teams, manager assignments, scope.
3. **Properties** — fast create, Google Places/geocode, categories, tags, assignments, search.
4. **Tasks & Scheduling** — task types, checklists, tasks, state machine, calendar, recurrence, notifications.
5. **Attendance, GPS & Approval** — shifts, attendance, GPS/geofence, evidence, approvals, incidents.
6. **Reports & Hardening** — dashboards, reports, exports, audit viewer, settings, API stabilization.

Web-first: each phase stabilizes before the next. API endpoints built alongside modules, gated by the same permission keys.

## Modules

| Module | Folder | Priority | Status |
|---|---|---|---|
| Foundation | [foundation](foundation/plan.md) | 1 | done |
| Personnel & Teams | [personnel](personnel/plan.md) | 2 | done (manager-assignment UI deferred) |
| Properties | [properties](properties/plan.md) | 2 | not started |
| Tasks & Scheduling | [tasks](tasks/plan.md) | 3 | not started |
| Attendance, GPS & Approval | [attendance-gps-approval](attendance-gps-approval/plan.md) | 4 | not started |
| Reports & Hardening | [reports](reports/plan.md) | 5 | not started |

Each module folder tracks its own `plan.md` (full plan), `current.md` (done/doing), `future.md` (deferred), `suggestion.md` (recommendations), `design.md` (architecture/schema).

## Cross-Cutting Rules (from spec §0 / AGENTS.md)

- Every protected route uses `permission:<key>` or `role:<roles>` middleware; public routes carry none.
- Permission keys map as: `1` Settings, `2` Personnel, `3` Properties, `4` Tasks, `5` Shifts, `6` Attendance, `7` Reports, `8` Incidents, `9` Audit.
- No hard deletes of records referenced by completed work. Use soft deletes + audit.
- Transactions for every multi-record write.
- Background jobs for geocoding, images, exports, notifications, sync.
- Domain logic in `app/Actions` / `app/Domain` / `app/Services`, never in controllers.
- Tests for every critical workflow (unit + feature); `php84 artisan test`.
- Per module: migration + model(s) + policy/permission + validation + action + web UI + API endpoints + tests + audit + seeds + docs.

## Definition of Done (spec §26)

Migration, relationships, authorization, validation, service/action, web UI, API, tests pass, audit entry, error/loading/empty states, docs updated, no high-severity security issue open.