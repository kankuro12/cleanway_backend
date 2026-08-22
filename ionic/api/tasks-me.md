# GET /api/v1/me/tasks

Current user's own tasks (my-task semantics, permission 4.1), ordered by `scheduled_start_at` ascending. Loads taskType, property, assignments, subtasks.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/me/tasks`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `status` | string | no | task status enum | Filter by status (single value) |
| `priority` | string | no | task priority values | Filter by priority |
| `from` | date | no | `YYYY-MM-DD` | Scheduled from (inclusive) |
| `to` | date | no | `YYYY-MM-DD` | Scheduled to (inclusive) |
| `per_page` | int | no | default 25 | Page size |

## Responses

### 200 — Success

`data` = array of TaskResource — full field list in [tasks-show](tasks-show.md).

```json
{
  "data": [
    {
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
      "checklist": [ // ponytail: snapshot_item_id added (see tasks-show)
        { "section": "Floors", "label": "Vacuum all floors", "type": "yes_no", "required": true },
        { "section": "Floors", "label": "Mop", "type": "yes_no", "required": false }
      ],
      "subtasks": [
        { "id": 1, "title": "Restock paper", "completed_at": null }
      ],
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
  ],
  "meta": {
    "pagination": {
      "total": 14,
      "per_page": 25,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

### 401 / 429

Standard envelopes.

## Notes

- Only tasks assigned to / in scope of the requesting user.
- `checklist` and `subtasks` are included on this endpoint (loaded).
