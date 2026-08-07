# DELETE /api/v1/personnel/{user}

Archive (soft-delete) a user. Sets status to `archived` and soft-deletes the record.

## Request

- **Method**: `DELETE`
- **Path**: `/api/v1/personnel/{user}` — `user` = numeric user id
- **Auth**: bearer token
- **Permission**: `2.4`
- **Throttle**: 120 req/min
- **Body**: none

## Responses

### 200 — Success

```json
{
  "data": null
}
```

### 422 — Deleting own account

```json
{
  "message": "You cannot delete your own account."
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

## Notes

- No hard delete: record remains with `archived` status (audit-friendly).
