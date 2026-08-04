# Tasks & Scheduling — Current

## Done

- Nothing (module not started).

## In Progress

- Nothing.

## Next

1. Migrations: `task_types`, `checklist_templates/_sections/_items`, `tasks`, `task_assignments`, `task_status_histories`, `task_recurrences`, `task_checklist_snapshots`, `notifications`, `notification_deliveries`.
2. Models + relationships (snapshot relations, assignments, history).
3. TaskType/Checklist CRUD (admin, `4.7`/`4.8`).
4. `CreateTask` action: validation (conflicts/availability/leave), snapshot creation, assignment, audit.
5. `TransitionTaskStatus` action + transition map + history writer.
6. Calendar UI (Blade/Livewire) + reschedule action.
7. `GenerateRecurringTasks` command + scheduler entry.
8. Notification service (queued) + preference table.
9. Seeds: sample task types, checklists, tasks.
10. Tests: transitions (valid + invalid), conflicts, snapshots, recurrence, notifications.