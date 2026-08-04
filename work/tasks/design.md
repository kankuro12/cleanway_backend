# Tasks & Scheduling — Design

## State machine (spec §9.2)

```
draft → scheduled → assigned → accepted → in_progress → completed → submitted_for_approval → approved
                      │             │            │
                      └ unassigned   ├ declined   ├ paused ⇄ in_progress
                                    │            ├ delayed
                                    └ cancelled  └ unable_to_access
correction_requested ⇄ in_progress | rejected → reopened → in_progress | cancelled
```

Rules: cleaner cannot approve own task; approvals only from `assigned`/`submitted_for_approval`/`correction_requested`; cancelled/reopened only by authorized (`4.6`).

## ER

```
task_types ─< tasks >── property (nullable, one-time location snapshots on task)
checklist_templates ─< checklist_sections ─< checklist_items
tasks ─< task_assignments (morph assignee user|team)
tasks ─< task_status_histories
tasks ─< task_checklist_snapshots (+ responses later in attendance phase)
task_recurrences: rule, start/end, property, assignee, type, checklist, notification timing
notifications: user_id, type, payload json, read_at, idempotency_key unique
notification_deliveries: notification_id, channel, status, attempts, delivered_at
```

## Snapshot flow

`CreateTask` → copy task type fields + checklist tree into `task_checklist_snapshots` → later edits to type/template ignored by existing tasks.

## Key services

- `app/Domain/Tasks/CreateTask.php`
- `app/Domain/Tasks/AssignTask.php`
- `app/Domain/Tasks/RescheduleTask.php`
- `app/Domain/Tasks/TransitionTaskStatus.php`
- `app/Domain/Tasks/GenerateRecurringTasks.php`
- `app/Domain/Tasks/TaskSchedulingValidator.php`
- `app/Services/Notifications/NotificationService.php`

## Tests

- Transition matrix: valid + invalid per state.
- Conflict/overlap/availability validation.
- Snapshot immutability after template change.
- Recurrence instance generation + no changes to completed instances.
- Notification idempotency.