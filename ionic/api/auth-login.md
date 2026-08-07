# POST /api/v1/auth/login

Obtain a bearer token. The **only** public endpoint.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/auth/login`
- **Auth**: none
- **Throttle**: 10 requests/min per IP
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |d
  "password": "password123",
  "device_name": "android-install-8f3a"
}
```

## Responses

### 200 — Success

`data.user` is the full user resource (same shape as `GET /me`). `data.token` is the Sanctum plain-text token — **store it securely and send as `Authorization: Bearer <token>`** on all subsequent calls. It is only shown once.

| Field | Type | Description |
|---|---|---|
| `data.user.id` | int | User id |
| `data.user.name` | string | Display name |
| `data.user.email` | string | Email |
| `data.user.role` | int | `0` admin, `1` supervisor, `2` cleaner |
| `data.user.status` | string | `invited`, `active`, `inactive`, `suspended`, `on_leave`, `archived` |
| `data.user.employee_no` | string\|null | Employee number |
| `data.user.phone` | string\|null | Phone |
| `data.user.employment_type` | string\|null | e.g. full_time / part_time |
| `data.user.branch_id` | int\|null | Branch |
| `data.user.team_id` | int\|null | Team |
| `data.user.manager_id` | int\|null | Reporting manager |
| `data.user.start_date` | date\|null | `YYYY-MM-DD` |
| `data.user.end_date` | date\|null | `YYYY-MM-DD` |
| `data.user.skills` | array\|null | Skill tags |
| `data.user.created_at` | datetime\|null | ISO 8601 |
| `data.token` | string | Plain-text bearer token |

```json
{
  "data": {
    "user": {
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
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz0123456789"
  }
}
```

### 422 — Bad credentials

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 422 — Validation failure

```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 429 — Throttled

```json
{
  "message": "Too Many Requests"
}
```

## Notes

- Use this response's `token` for all other endpoints; never re-login on every request.
- On logout, the token is revoked server-side — treat login as one-shot.
