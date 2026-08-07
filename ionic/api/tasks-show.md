# GET /api/v1/tasks/{task}

Single task detail. Loads taskType, property, assignments.assignee, checklistSnapshot, subtasks.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/tasks/{task}` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.1`
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success — TaskResource (canonical field list)

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Task id |
| `data.uuid` | string | UUID |
| `data.reference_number` | string | Human reference e.g. `TSK-2026-0001` |
| `data.title` | string | Title |
| `data.description` | string\|null | Description |
| `data.task_type_id` | int\|null | Task type id |
| `data.task_type` | object\|null | `{"id", "name"}` |
| `data.property_id` | int\|null | Property id |
| `data.property` | object\|null | `{"id", "name"}` |
| `data.location` | object | `{property_name_snapshot, address_snapshot, latitude, longitude, check_in_radius_meters}` — snapshot at assignment |
| `data.scheduled_start_at` | datetime\|null | ISO 8601 |
| `data.scheduled_end_at` | datetime\|null | ISO 8601 |
| `data.estimated_duration_minutes` | int\|null | Duration |
| `data.priority` | string | Priority value |
| `data.status` | string | task status enum |
| `data.approval_required` | bool | Requires approval (auto-submit on complete) |
| `data.task_type_snapshot` | object\|null | Snapshot of type config |
| `data.checklist` | array\|null | `[{section, label, type, required}]` — checklist item type enum |
| `data.subtasks` | array | `[{id, title, completed_at}]` |
| `data.assignments` | array | `[{id, assignee_type, assignee_id, assignee{id,name}, status}]` |
| `data.accepted_at` | datetime\|null | ISO 8601 |
| `data.started_at` | datetime\|null | ISO 8601 |
| `data.completed_at` | datetime\|null | ISO 8601 |
| `data.submitted_at` | datetime\|null | ISO 8601 |
| `data.approved_at` | datetime\|null | ISO 8601 |
| `data.created_at` | datetime | ISO 8601 |
| `data.updated_at` | datetime | ISO 8601 |

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
    "status": "assigned",
    "approval_required": true,
    "task_type_snapshot": { "name": "Full Clean" },
    "checklist": [
      { "section": "Floors", "label": "Vacuum all floors", "type": "yes_no", "required": true },
      { "section": "Floors", "label": "Mop", "type": "yes_no", "required": false }
    ],
    "subtasks": [{ "id": 1, "title": "Restock paper", "completed_at": null }],
    "assignments": [
      { "id": 88, "assignee_type": "user", "assignee_id": 6, "assignee": { "id": 6, "name": "Jordan Cleaner" }, "status": "accepted" }
    ],
    "accepted_at": null,
    "started_at": null,
    "completed_at": null,
    "submitted_at": null,
    "approved_at": null,
    "created_at": "2026-08-05T09:00:00+00:00",
    "updated_at": "2026-08-07T10:00:00+00:00"
  }
}
```

### 403 — No 4.1

```json
{
  "message": "You do not have permission to perform this action."
}
```

### 404 — Not found

```json
{
  "message": "Not Found"
}
```

### 401 / 429

Standard envelopes.
