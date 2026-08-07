# POST /api/v1/personnel

Create a personnel user (admin).

## Request

- **Method**: `POST`
- **Path**: `/api/v1/personnel`
- **Auth**: bearer token
- **Permission**: `2.2`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `name` | string | yes | ≤255 | Full name |
| `email` | string | yes | valid email, ≤255, unique | Login email |
| `password` | string | yes | min 8 | Login password |
| `role` | int | yes | `0`\|`1`\|`2` | Role |
| `status` | string | yes | user status enum | Initial status |
| `employee_no` | string | no | ≤50, unique | Employee number |
| `phone` | string | no | ≤30 | Phone |
| `branch_id` | int | no | exists branches | Branch |
| `team_id` | int | no | exists teams | Team |
| `manager_id` | int | no | exists users | Reporting manager |
| `employment_type` | string | no | ≤30 | e.g. full_time |
| `start_date` | date | no | `YYYY-MM-DD` | Start date |
| `end_date` | date | no | ≥ start_date | End date |
| `emergency_contact` | array | no | — | Emergency contact object |
| `skills` | array | no | — | Skill tags |
| `certifications` | array | no | — | Certifications |
| `default_working_hours` | array | no | — | Working hours config |
| `service_areas` | array | no | — | Service areas |
| `notification_preferences` | array | no | — | Notification prefs |

### Request example

```json
{
  "name": "New Cleaner",
  "email": "new.cleaner@cleanway.test",
  "password": "password123",
  "role": 2,
  "status": "active",
  "employee_no": "EMP-015",
  "phone": "0215559999",
  "branch_id": 1,
  "team_id": 3,
  "manager_id": 2,
  "employment_type": "full_time",
  "start_date": "2026-01-01",
  "skills": ["retail"]
}
```

## Responses

### 201 — Created

```json
{
  "data": {
    "id": 55,
    "name": "New Cleaner",
    "email": "new.cleaner@cleanway.test",
    "role": 2,
    "status": "active",
    "employee_no": "EMP-015",
    "phone": "0215559999",
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

### 422 — Validation

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### 401 / 403 / 429

Standard envelopes.
