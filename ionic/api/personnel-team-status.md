# GET /api/v1/supervisor/team-status

Supervisors' and cleaners' roster with branch/team names — for team status rails.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/supervisor/team-status`
- **Auth**: bearer token
- **Permission**: `2.1`
- **Throttle**: 120 req/min
- **Query params**: none (no pagination — full roster)

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data[].id` | int | User id |
| `data[].name` | string | Name |
| `data[].role` | int | `1` supervisor or `2` cleaner |
| `data[].status` | string | user status enum |
| `data[].branch_id` | int\|null | Branch id |
| `data[].team_id` | int\|null | Team id |
| `data[].branch` | object\|null | `{"id": int, "name": string}` |
| `data[].team` | object\|null | `{"id": int, "name": string}` |

```json
{
  "data": [
    {
      "id": 6,
      "name": "Jordan Cleaner",
      "role": 2,
      "status": "active",
      "branch_id": 1,
      "team_id": 3,
      "branch": { "id": 1, "name": "Auckland Central" },
      "team": { "id": 3, "name": "CBD Night Crew" }
    }
  ]
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- Only roles 1 and 2 appear (admins excluded).
- No `meta.pagination` — response is the complete list.
- Ordered by `name` ascending.
