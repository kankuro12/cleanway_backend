# DELETE /api/v1/me/devices/{token}

Unregister a device token (own tokens only). Use on logout to stop push.

## Request

- **Method**: `DELETE`
- **Path**: `/api/v1/me/devices/{token}` — `token` = the FCM token string, **URL-encoded** (contains `:` and `_`)
- **Auth**: bearer token
- **Permission**: any authenticated user (own devices)
- **Throttle**: 120 req/min
- **Body**: none

## Responses

### 200 — Success

```json
{
  "data": null
}
```

### 401 / 429

Standard envelopes.

## Notes

- Deleting a token that does not exist still returns 200 (no-op).
- Only removes devices belonging to the requesting user.
- Example path: `/api/v1/me/devices/dQw4w9WgXcQ%3AAPA91b...`
