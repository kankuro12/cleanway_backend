# Tasks & Scheduling — Suggestions

- **State machine as single map**: `app/Domain/Tasks/TaskStatus.php` with `transitions: [from => [to => [permission, validation-rule]]]` — one file, unit-testable, no scattered `if`s (spec §9.2 "no arbitrary status updates").
- **Snapshot checklist at creation** (spec §8/§9.4): copy type + checklist into task rows; template edits never touch existing tasks.
- **Reference numbers**: `TASK-YYYYMM-####` via sequence table or DB counter — unique + human-readable.
- **Conflict check scope**: same property + overlapping time windows + assigned user availability; make it a service (`TaskSchedulingValidator`) so web form and API share it.
- **Notifications**: delivery log with idempotency key (event type + entity id) — prevents duplicates on job retries (spec §14).
- **Calendar**: server-rendered month view first; drag-drop reschedule as Livewire enhancement.
- **Indexes**: tasks(status, scheduled_start_at), tasks(property_id), task_assignments(assignee_type, assignee_id), status_histories(task_id, created_at).
- **Queries**: task list selects only needed columns; eager-load assignments + property snapshot only.