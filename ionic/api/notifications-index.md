# GET /api/v1/notifications

Current user's notifications, newest first, paginated. Tab-style filtering via `read` param.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/notifications`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `read` | int | no | `0` or `1` | `0` → unread only; `1` → read only; **absent** → all |
| `per_page` | int | no | default 25 | Page size |

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data[].id` | int | Notification id |
| `data[].type` | string | Notification type key |
| `data[].title` | string | Title |
| `data[].body` | string\|null | Body text |
| `data[].payload` | object\|null | Route/target data (e.g. task id for deep links) |
| `data[].read` | bool | Read state |
| `data[].created_at` | datetime | ISO 8601 |

```json
{
  "data": [
    {
      "id": 900,
      "type": "task.assigned",
      "title": "New task assigned",
      "body": "Night clean — Sky Tower Plaza on 07 Aug 10:00 PM",
      "payload": { "task_id": 501 },
      "read": false,
      "created_at": "2026-08-07T12:00:00+00:00"
    }
  ],
  "meta": {
    "pagination": {
      "total": 18,
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

- `payload` keys vary by `type` — use `task_id` when present for task deep links.
- Unread count for badges: call with `read=0` and read `meta.pagination.total`.
