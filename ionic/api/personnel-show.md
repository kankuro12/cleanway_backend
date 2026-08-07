# GET /api/v1/personnel/{user}

Single user resource.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/personnel/{user}` — `user` = numeric user id
- **Auth**: bearer token
- **Permission**: `2.1`
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success

Same user resource shape as `GET /me`.

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

### 404 — Not found

```json
{
  "message": "Not Found"
}
```

### 401 / 403 / 429

Standard envelopes.
