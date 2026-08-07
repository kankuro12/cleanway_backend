# POST /api/v1/tasks/{task}/transition

Advance a task through its state machine. **Key behavior**: completing an `approval_required` task auto-submits it (`submitted_for_approval`) server-side — the response reflects the final state.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `status` | string | yes | `accepted` \| `declined` \| `start` \| `pause` \| `resume` \| `complete` \| `submit` | Desired transition |
| `remarks` | string | no | ≤1000 | Note attached to the transition |
| `latitude` | float | no | −90..90 | Location at transition |
| `longitude` | float | no | −180..180 | Location at transition |

### Request example

```json
{
  "status": "complete",
  "remarks": "Done — spotless.",
  "latitude": -36.8485,
  "longitude": 174.7624
}
```

## Responses

### 200 — Success

TaskResource (fields in [tasks-show](tasks-show.md)) with assignments loaded. For an approval-required task completed above, expect `status: "submitted_for_approval"` and `submitted_at` set.

```json
{
  "data": {
    "id": 501,
    "uuid": "c3d4e5f6-...",
    "reference_number": "TSK-2026-0001",
    "title": "Night clean — Sky Tower Plaza",
    "description": "Full floor clean, bins, glass.",
    "task_type_id": 3,
    "task_type": { "id": 3, "name": "Full Clean" },
    "property_id": 12,
    "property": { "id": 12, "name": "Sky Tower Plaza" },
    "location": {
      "property_name_snapshot": "Sky Tower Plaza",
      "address_snapshot": "100 Victoria St W, Auckland",
      "latitude": -36.8485,
      "longitude": 174.7624,
      "check_in_radius_meters": 120
    },
    "scheduled_start_at": "2026-08-07T22:00:00+00:00",
    "scheduled_end_at": "2026-08-08T02:00:00+00:00",
    "estimated_duration_minutes": 240,
    "priority": "high",
    "status": "submitted_for_approval",
    "approval_required": true,
    "task_type_snapshot": { "name": "Full Clean" },
    "checklist": [],
    "subtasks": [],
    "assignments": [
      { "id": 88, "assignee_type": "user", "assignee_id": 6, "assignee": { "id": 6, "name": "Jordan Cleaner" }, "status": "accepted" }
    ],
    "accepted_at": null,
    "started_at": null,
    "completed_at": "2026-08-08T02:05:00+00:00",
    "submitted_at": "2026-08-08T02:05:00+00:00",
    "approved_at": null,
    "created_at": "2026-08-05T09:00:00+00:00",
    "updated_at": "2026-08-08T02:05:00+00:00"
  }
}
```

### 422 — Illegal transition (state machine rejection)

`message` carries the human-readable reason (e.g. cannot start before accepted, cannot complete with missing evidence).

```json
{
  "message": "Task cannot transition from 'assigned' to 'complete'."
}
```

### 403 / 404 / 401 / 429

Standard envelopes (403 = no 4.4; 404 = task missing).

## Notes

- Valid action order is enforced server-side; the API is the source of truth — refresh task after each transition.
- Send the transition only when the current status allows it; a client may pre-check but must handle 422 gracefully.
