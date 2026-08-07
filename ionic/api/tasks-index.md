# GET /api/v1/tasks

All tasks (Task List — web mirror), permission `4.9`. Ordered by `scheduled_start_at` descending. Loads taskType, property, assignments, subtasks.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/tasks`
- **Auth**: bearer token
- **Permission**: `4.9`
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `status` | string | no | task status enum | Filter by status |
| `priority` | string | no | priority values | Filter by priority |
| `task_type_id` | int | no | exists task_types | Filter by task type |
| `property_id` | int | no | exists properties | Filter by property |
| `assignee_id` | int | no | exists users | Filter by assignee |
| `from` | date | no | `YYYY-MM-DD` | Scheduled from (inclusive) |
| `to` | date | no | `YYYY-MM-DD` | Scheduled to (inclusive) |
| `per_page` | int | no | default 25 | Page size |

## Responses

### 200 — Success

`data` = array of TaskResource — full field list in [tasks-show](tasks-show.md). Same item shape as `GET /me/tasks`.

```json
{
  "data": [],
  "meta": {
    "pagination": {
      "total": 0,
      "per_page": 25,
      "current_page": 1,
      "last_page": 0
    }
  }
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- Filter params combine (AND).
- This is the endpoint the 4.9 "Task List" tab and all-tasks filter sheets use.
