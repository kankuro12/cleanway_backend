# POST /api/v1/tasks/{task}/complete

Complete a task with checklist responses. The server verifies **every checklist item** is fulfilled (either completed in the task checklist, or answered via `responses`) — any unfulfilled item fails the request with its label.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}/complete` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `responses` | array | no | — | Checklist answers (see below) |
| `responses[].snapshot_item_id` | int | yes (per item) | exists in task checklist snapshot | Which checklist item |
| `responses[].value` | string | yes (per item) | ≤5000 | Answer — for yes_no/pass_fail use `yes`/`no`/`pass`/`fail`; free text for text; number string for numeric |
| `remarks` | string | no | ≤5000 | Completion notes |
| `latitude` | float | no | −90..90 | Completion location |
| `longitude` | float | no | −180..180 | Completion location |

### Request example

```json
{
  "responses": [
    { "snapshot_item_id": 12, "value": "yes" },
    { "snapshot_item_id": 13, "value": "pass" }
  ],
  "remarks": "All done, key returned to reception.",
  "latitude": -36.8485,
  "longitude": 174.7624
}
```

## Responses

### 200 — Success

TaskResource (fields in [tasks-show](tasks-show.md)). On approval-required tasks the task moves to `submitted_for_approval` (same auto-submit policy as transition).

```json
{
  "data": {
    "id": 501,
    "reference_number": "TSK-2026-0001",
    "title": "Night clean — Sky Tower Plaza",
    "status": "submitted_for_approval",
    "approval_required": true,
    "completed_at": "2026-08-08T02:05:00+00:00",
    "submitted_at": "2026-08-08T02:05:00+00:00",
    "updated_at": "2026-08-08T02:05:00+00:00"
  }
}
```

### 422 — Unfulfilled checklist items

`errors.task` lists the labels of every checklist item that is not yet fulfilled.

```json
{
  "message": "Task cannot be completed.",
  "errors": {
    "task": ["Requirement not fulfilled: Vacuum all floors", "Requirement not fulfilled: Clean windows"]
  }
}
```

### 422 — Validation

```json
{
  "message": "The responses.0.value field is required.",
  "errors": {
    "responses.0.value": ["The responses.0.value field is required."]
  }
}
```

### 403 / 404 / 401 / 429

Standard envelopes.

## Notes

- Client should pre-validate all checklist items against the task's `checklist` (from detail) to avoid the 422 round-trip.
- Snapshot ids come from `data.checklist` — but note the checklist in TaskResource does **not** expose the snapshot_item_id; obtain it from the same snapshot source the web app uses, or treat 422 `errors.task` as the authoritative missing list. (If the app needs ids, this is a backend addition — see notes in `ionic/plan.md`.)
