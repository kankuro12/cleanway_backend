# Admin Console

Objective: supervisor/admin dashboard + attention queue + approvals on phone — the management screens (mirror web dashboard widgets + approval queue). Backend additions required (all permission-gated, reuse existing domain services).

## Screens & structures

1. **Dashboard tab** (supervisor/admin) — stat cards (tap-to-filter behavior parity: Active tasks / Tasks today / Pending approval per role — same `filter` keys as web `DashboardWidgets`); attention queue (severity pills: gps / pending / incident severity + Review links, same labels as web); refresh on tab enter + pull-to-refresh.
2. **Approvals queue** (4.5) — submitted-for-approval + correction-requested tasks (web parity), card per item (property, assignee, submitted time, reason/remarks), approve/reject actions w/ reason capture.
3. **Incidents list** (8.2, backend add) — incidents w/ severity + status badges, filter (severity, status, category), detail view.

## APIs used

Existing: `GET /tasks` (4.9, full filters — powers filtered drill-downs from stat cards), `GET /me`, `GET /tasks/{id}`. Backend adds: `GET /dashboard/widgets`, `GET /approvals`, `POST /approvals/{id}/decision` (4.5), `GET /incidents` + `GET /incidents/{id}` (8.2).

## Forms

| Form | Fields | Notes |
|---|---|---|
| Approval decision | decision (approve/reject), reason (optional) | 4.5; confirm dialog for reject |
| Incidents filter | severity, status, category | → query params |
| Review navigation | (tap) | deep link to task/incident detail |

## Flows

- **Stat card tap** → drill into All Tasks with the widget's filter key applied (web parity).
- **Approval** → decision → queue refreshes; rejected task visible in reviewer's all-tasks list.

## Exit criteria

Supervisor approves/rejects from phone; stat numbers match web; attention queue severity pills render identically; incidents browseable (backend adds live).
