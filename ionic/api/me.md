# GET /api/v1/me

Current authenticated user's profile. Use at app launch to get role, status, and permission-relevant fields (role drives tab visibility).

## Request

- **Method**: `GET`
- **Path**: `/api/v1/me`
- **Auth**: `Authorization: Bearer <token>` (any authenticated user)
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data.id` | int | User id |
| `data.name` | string | Display name |
| `data.email` | string | Email |
| `data.role` | int | `0` admin, `1` supervisor, `2` cleaner |
| `data.status` | string | `invited`, `active`, `inactive`, `suspended`, `on_leave`, `archived` |
| `data.employee_no` | string\|null | Employee number |
| `data.phone` | string\|null | Phone |
| `data.employment_type` | string\|null | e.g. full_time / part_time |
| `data.branch_id` | int\|null | Branch id |
| `data.team_id` | int\|null | Team id |
| `data.manager_id` | int\|null | Reporting manager user id |
| `data.start_date` | date\|null | `YYYY-MM-DD` |
| `data.end_date` | date\|null | `YYYY-MM-DD` |
| `data.skills` | array\|null | Skill tags |
| `data.created_at` | datetime\|null | ISO 8601 |

```json
{
  "data": {
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
}
```

### 401 — Unauthenticated

```json
{
  "message": "Unauthenticated."
}
```

## Notes

- Response has **no `meta`** — it is a single object, not a list.
- If `data.status` is not `active`, decide whether to block app use client-side (server enforces nothing here; permission middleware still applies to other routes).
