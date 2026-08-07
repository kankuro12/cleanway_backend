# GET /api/v1/me/shifts

Current user's shifts, newest first, paginated. Loads property. Includes computed summary (worked/break/overtime + flags).

## Request

- **Method**: `GET`
- **Path**: `/api/v1/me/shifts`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Query params**: `per_page` (int, default 25)

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data[].id` | int | Shift id |
| `data[].date` | date | `YYYY-MM-DD` |
| `data[].scheduled_start_at` | datetime | ISO 8601 |
| `data[].scheduled_end_at` | datetime | ISO 8601 |
| `data[].property` | object\|null | `{"id", "name"}` |
| `data[].status` | string | `scheduled` \| `confirmed` \| `in_progress` \| `completed` \| `missed` |
| `data[].summary.worked_minutes` | int | Worked time |
| `data[].summary.break_minutes` | int | Break time |
| `data[].summary.overtime_minutes` | int | Overtime |
| `data[].summary.late` | bool | Late flag |
| `data[].summary.early_departure` | bool | Left early flag |
| `data[].summary.missed` | bool | Missed flag |

```json
{
  "data": [
    {
      "id": 214,
      "date": "2026-08-07",
      "scheduled_start_at": "2026-08-07T22:00:00+00:00",
      "scheduled_end_at": "2026-08-08T02:00:00+00:00",
      "property": { "id": 12, "name": "Sky Tower Plaza" },
      "status": "in_progress",
      "summary": {
        "worked_minutes": 90,
        "break_minutes": 15,
        "overtime_minutes": 0,
        "late": false,
        "early_departure": false,
        "missed": false
      }
    }
  ],
  "meta": {
    "pagination": {
      "total": 42,
      "per_page": 25,
      "current_page": 1,
      "last_page": 2
    }
  }
}
```

### 401 / 429

Standard envelopes.

## Notes

- Ordered by `date` descending.
- Use the latest shift's status + summary to drive the clock screen's state.
