# POST /api/v1/notifications/{notification}/read

Mark one of your notifications as read.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/notifications/{notification}/read` — `notification` = numeric notification id
- **Auth**: bearer token
- **Permission**: any authenticated user (owner only)
- **Throttle**: 120 req/min
- **Body**: none (empty JSON `{}` accepted)

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Notification id |
| `data.read` | bool | `true` |

```json
{
  "data": {
    "id": 900,
    "read": true
  }
}
```

### 403 — Not the owner

```json
{
  "message": "You do not have permission to perform this action."
}
```

### 404 — Not found

```json
{
  "message": "Not Found"
}
```

### 401 / 429

Standard envelopes.

## Notes

- Idempotent: marking an already-read notification returns 200.
