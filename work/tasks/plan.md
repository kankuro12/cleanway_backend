# Tasks & Scheduling

Objective: configurable task types + checklists, one-time + recurring tasks with explicit state machine, calendar scheduling, queued notifications (spec §8, §9, §10, §14).

## Scope

1. **Task types** — `task_types` (name, slug, description, default duration, priority, instructions, default checklist, before/after photo requirements, min photo count, approval required, allowed assignee types, active, sort order).
2. **Checklist templates** — `checklist_templates`, `_sections`, `_items` (ordered, required, yes/no, pass/fail, text, numeric, photo-required, issue-triggering, completion rules).
3. **Tasks** — per spec §9.1: uuid, reference_number, title, description, task_type_id, property_id (nullable), location snapshots (name/address/lat/lng/radius), assigned_manager_id, schedule, duration, priority, status, recurrence_rule, approval_required, acceptance/timestamps, created_by/updated_by, soft deletes.
4. **Task assignments** — `task_assignments` (task_id, assignee_type/id — user or team, assigned_at, assigned_by, status: pending|accepted|declined).
5. **State machine** (spec §9.2) — explicit transition map, `TransitionTaskStatus` action, every transition writes `task_status_histories` (previous/new status, user, timestamp, remarks, device, lat/lng, source).
6. **Creation validation** — scheduling conflicts, availability, overlapping assignments, leave, skills, travel-time warnings; warnings overridable with recorded reason.
7. **Calendar** — daily/weekly/monthly/personnel/team/property/manager views; drag-and-drop reschedule; filters; unscheduled task queue; shift overlay (shift data from attendance phase).
8. **Recurring tasks** — `task_recurrences` (rule, start/end, time, property, default assignee, task type, checklist, notification timing); `GenerateRecurringTasks` command generates instances ahead; template changes never modify completed instances.
9. **Checklist snapshots** — task_type + checklist copied onto task at creation; later template changes don't alter existing tasks (spec §8, §9.4).
10. **Notifications** — `notifications` + `notification_deliveries`; events per spec §14 (assigned, accepted, declined, upcoming, overdue, cancelled, correction requested, approved…); preferences, quiet hours, queued delivery.

## Permissions used

`4.1` view, `4.2` create, `4.3` assign, `4.4` update status, `4.5` approve, `4.6` cancel/reopen, `4.7` task types, `4.8` checklists.

## API

`/api/v1/me/tasks`, `/api/v1/tasks/{task}` + accept/decline/start/pause/resume/complete/submit/check-in/check-out, notifications read. (Check-in/check-out implemented in attendance phase.)

## Exit criteria (spec §25 Phase 2)

Manager schedules + assigns; conflicts detected; history auditable; notifications queued.