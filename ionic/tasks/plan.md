# Tasks

Objective: the cleaner's core field workflow — see own tasks, do the work with GPS + evidence, submit; plus the all-tasks list (4.9) and minimal task create (4.2). Mirrors web task screens exactly (status semantics, filter set, action strips).

## Screens & structures

1. **My Tasks list** (tab, 4.1) — `current` / `finished` segments matching web tabs; cards: title, property name, task type, priority, scheduled start, status badge (dot + mono label, tinted); pull-to-refresh + infinite scroll (`per_page`).
2. **All Tasks list** (4.9) — same card; filter sheet (status, priority, task_type_id, property_id, assignee_id, from, to) + search; active-filter pills w/ 1-tap remove (web pattern).
3. **Task detail** — description, property (+ tap → maps with address), checklist snapshot (items w/ type: yes/no, pass/fail, text, numeric, photo), subtasks, status history, action strip gated by permission + state machine.
4. **Task create** (4.2, minimal) — compact form; POST mirrors web task-create fields; success → detail.
5. **Incident report** — from task detail overflow; category/severity pickers (enum values from API), description, auto GPS; photos deferred until backend supports them (see master plan backend note).
6. **Evidence viewer** — thumbnail grid of uploaded evidence w/ type labels (from task detail payload).

## APIs used

`GET /me/tasks`, `GET /tasks` (4.9), `GET /tasks/{id}`, `POST /tasks/{id}/transition`, `POST /tasks/{id}/check-in`, `POST /tasks/{id}/check-out`, `POST /tasks/{id}/evidence`, `POST /tasks/{id}/complete`, `POST /tasks/{id}/incidents` — fields in master inventory.

## Forms

| Form | Fields | Notes |
|---|---|---|
| Transition | status (from action strip), remarks (optional), latitude, longitude (auto) | complete on approval-required task → app shows "submitted" state from response |
| Check-in/out | GPS fields (lat, lng, accuracy, device_timestamp, device_id, offline, is_mock_location) | blocked → 403 w/ exception shown as warning banner |
| Evidence | photo file, evidence_type, captured_at, lat/lng, device_id | camera + gallery pickers |
| Complete | responses per snapshot_item_id (value), remarks, lat/lng | missing items → 422 lists them; surface as "unfinished items" prompt |
| Incident | category (enum), severity (enum), description, lat/lng | ≤5000 chars |
| Task create (4.2) | title, description, task_type_id, property_id, priority, scheduled_start_at, assignee_id | mirror web fields |

## Flows

- **Field completion**: open detail → check-in (GPS auto) → do work → evidence photos → complete (checklist responses) → auto-submit handled by backend → card moves to finished/submitted.
- **Offline**: check-in/evidence/complete/incident enqueue (Phase 8); GPS + timestamp captured at action time.
- **Filters**: sheet → apply → query params on `GET /tasks`; pills remove one filter at a time.

## Exit criteria

Cleaner completes a task end-to-end with GPS + evidence on both platforms; approval-required task lands in submitted state; all-tasks filters work for 4.9 users; offline queue smoke-tested (Phase 8 gate).
