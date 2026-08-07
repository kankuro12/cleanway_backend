# PUT /api/v1/personnel/{user}

Update a personnel user (admin). All fields optional — only provided fields change.

## Request

- **Method**: `PUT`
- **Path**: `/api/v1/personnel/{user}` — `user` = numeric user id
- **Auth**: bearer token
- **Permission**: `2.3`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

Same field set as `POST /personnel` (personnel-store doc), all **optional** (`sometimes` semantics). Constraints identical; `email` and `employee_no` uniqueness **ignores the updated user itself**.

### Request example

```json
{
  "phone": "0215557777",
  "status": "on_leave"
}
```

## Responses

### 200 — Success

Full updated user resource.

```json
{
  "data": {
    "id": 55,
    "name": "New Cleaner",
    "email": "new.cleaner@cleanway.test",
    "role": 2,
    "status": "on_leave",
    "employee_no": "EMP-015",
    "phone": "0215557777",
    "employment_type": "full_time",
    "branch_id": 1,
    "team_id": 3,
    "manager_id": 2,
    "start_date": "2026-01-01",
    "end_date": null,
    "skills": ["retail"],
    "created_at": "2026-01-01T00:00:00+00:00"
  }
}
```

### 422 / 404 / 401 / 403 / 429

Standard envelopes (404 when user missing).
