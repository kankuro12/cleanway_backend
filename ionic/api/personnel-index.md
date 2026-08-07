# GET /api/v1/personnel

Paginated personnel list with filters. Scoped server-side: users see only users in their management scope.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/personnel`
- **Auth**: bearer token
- **Permission**: `2.1`
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `search` | string | no | — | Matches name/email (server implementation) |
| `role` | int | no | `0`\|`1`\|`2` | Filter by role |
| `status` | string | no | user status enum | Filter by status |
| `branch_id` | int | no | exists | Filter by branch |
| `per_page` | int | no | default 25 | Page size |

## Responses

### 200 — Success

`data` is an array of user resources (fields identical to `GET /me`). This endpoint **additionally** returns `links` (pagination link object) alongside `meta`.

```json
{
  "data": [
    {
      "id": 6,
      "name": "Jordan Cleaner",
      "email": "jordan.cleaner@cleanway.test",
      "role": 2,
      "status": "active",
      "employee_no": "EMP-014",
      "phone": "0215551234",
      "employment_type": "full_time",
      "branch_id": 1,
      "team_id": 3,
      "manager_id": 2,
      "start_date": "2025-01-15",
      "end_date": null,
      "skills": ["car_park", "retail"],
      "created_at": "2025-01-15T09:00:00+00:00"
    }
  ],
  "meta": {
    "pagination": {
      "total": 42,
      "per_page": 25,
      "current_page": 1,
      "last_page": 2
    }
  },
  "links": {
    "first": "https://app/api/v1/personnel?page=1",
    "last": "https://app/api/v1/personnel?page=2",
    "prev": null,
    "next": "https://app/api/v1/personnel?page=2"
  }
}
```

### 401 / 403 / 429

Standard envelopes (`message` only; see index).

## Notes

- Ordered by `name` ascending.
- Empty result → `"data": []`, pagination meta with `total: 0`.
