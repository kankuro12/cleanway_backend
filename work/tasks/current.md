# Tasks & Scheduling — Current

## Done

- [x] Migrations: `task_types`, `checklist_templates`/`_sections`/`_items`, `tasks` (full §9.1 contract + type snapshot json + one-time location + radius snapshot), `task_assignments` (morph), `task_status_histories`, `task_checklist_snapshots` (flattened), `task_recurrences`, `notifications`, `notification_deliveries`.
- [x] Models + relations; morph map registered in `AppServiceProvider` (`user`/`team`/`branch` aliases).
- [x] Task types CRUD (web, `4.7`).
- [x] Checklist templates CRUD with dynamic sections/items editor (web, `4.8`).
- [x] `CreateTask` action: task-type + checklist snapshotting (immutable), property or one-time location, radius snapshot via `EffectiveRadiusResolver`, assignment in same transaction, audit, assignment notification.
- [x] `TaskSchedulingValidator`: conflicts, availability, leave, skills — warnings overridable with recorded reason.
- [x] `AssignTask` (+remove, unassigned status), `RescheduleTask` (overlap warning + schedule-changed notification).
- [x] `TransitionTaskStatus`: explicit transition map (design.md), permission gating (`4.4`/`4.5`/`4.6`), no self-approval, cleaner only own tasks, per-transition timestamps, `task_status_histories` rows, audit, notifications.
- [x] Calendar: FullCalendar month/week/list views + `/calendar/events` JSON (filters, status colours).
- [x] `GenerateRecurringTasks` + `tasks:generate-recurring` command + nightly scheduler (30-day horizon, idempotent, RRULE-style FREQ/INTERVAL parsing, completed instances never modified).
- [x] `NotificationService`: in-app writer, idempotency keys, per-channel `notification_deliveries` log (non-in-app channels marked skipped for later mail/push/SMS).
- [x] Email delivery: `TaskAssignedMail` (queued, branded template with task/location/schedule/subtasks) — sent to every person on assignment; delivery row logged per channel (`email` marked sent when queued). Team assignments notify nothing (no member fan-out yet).
- [x] Web UI: task register w/ filters, sectioned create form, task edit (details/reschedule + status machine + assignments + immutable checklist + history), task types, checklists, calendar, recurrences, notifications inbox.
- [x] My Tasks / Task List split: `4.9` (view all) granted admin+supervisor; `/admin/my-tasks` (own, two tabs current/finished) vs `/admin/tasks` (all, `4.9`); sidebar + back-links route by permission; cleaner hitting all-tasks → 403.
- [x] API: `/me/tasks` (own), `/tasks` (all, `4.9`), `/tasks/{task}`, `/tasks/{task}/transition` (accept/decline/start/pause/resume/complete/submit), `/notifications` + read (`?read=1|0` mirrors web tabs); `TaskResource`. Check-in/check-out/evidence/incidents via TaskGpsController. Transition `complete` auto-submits when `approval_required` — same policy as web.
- [x] Approval-required default: migrations + `CreateTask` default `approval_required = true`; completing work auto-transitions to `submitted_for_approval` (web `CompleteTask` + API transition); finished = [completed, approved, rejected, cancelled] — pending approval stays in Current tab.
- [x] Attendance on completion: `CompleteTask` records a `clock_out` AttendanceEvent unless one already exists (shared by web + API).
- [x] Notifications inbox: Unread tab (default, server-rendered) + Read tab (lazy-loaded AJAX feed); mark-read / mark-all-read via axios without reload; `NotificationController::markRead/markAllRead` return JSON for AJAX, redirect otherwise.
- [x] Seeder: `TasksSeeder` (checklist template, 2 task types, 2 tasks w/ subtasks, 1 recurrence).
- [x] Tests: 14 (create+notify+snapshot, one-time location, snapshot immutability, invalid transition, full approval flow, no self-approval, unassigned cleaner block, conflict warnings + override, recurrence idempotency, completed-instance immutability, API me/tasks + transition, web filter, permission, notification read).

## Task form upgrades

- [x] `task_subtasks` table + model + Task relation (title, completed_at/by, sort_order).
- [x] `CreateTask`: title auto-derived from property/location (manual title removed from form); multiple assignees (`assignee_ids[]`) + optional team; subtasks created in same transaction.
- [x] Store/Update requests: `assignee_ids`, `team_id`, `subtasks[]`; title now optional.
- [x] Web create form: property-first card with Select2 (server-side `/properties/options` search), address/lat/lng autofill on select, inline "Add property here" (only when user holds `3.2`), recurrence card auto-hidden behind toggle, assignees multi-select2, sub tasks dynamic-row card.
- [x] Edit page: sub tasks list (toggle done/reopen at `4.4`), add sub task (separate `4.4` route so cleaners can add).
- [x] API: `TaskResource` exposes `subtasks` (me/tasks + show eager-load them).
- [x] Tests: 6 (`TaskSubtaskModuleTest` — auto title, multi-assignee + per-user notifications, subtask creation, toggle, cleaner add, API shape).
- [x] Seeder uses multi-assignee + subtasks for the sample weekly clean.

## Verified

- 153 tests green, pint clean, all views compile.
- Multi-assignee: one task, N people each notified; legacy single `assignee_id` still supported (API compat).
- Permission overrides (`user_permissions`) apply everywhere — web + API go through `User::hasPermission`.

## Next

1. Attendance phase: check-in/check-out actions use `check_in_radius_snapshot` + `EffectiveRadiusResolver`; evidence + incidents reference tasks.
2. Drag-and-drop calendar reschedule + shift overlay (attendance phase provides shifts).
3. Email/push/SMS channels behind `notification_deliveries` stubs.