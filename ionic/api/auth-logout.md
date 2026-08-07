# POST /api/v1/auth/logout

Revoke the current bearer token. After this, the token is invalid.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/auth/logout`
- **Auth**: `Authorization: Bearer <token>` (any authenticated user)
- **Throttle**: 120 req/min
- **Content-Type**: `application/json` (empty body)

## Responses

### 200 — Success

```json
{
  "data": null
}
```

### 401 — Unauthenticated

```json
{
  "message": "Unauthenticated."
}
```

## Notes

- Only the token used for the request is revoked (current access token).
- Client should also delete the stored token locally.
