# DELETE /api/v1/properties/{property}

Archive a property: sets `active=false` and soft-deletes. Logs audit `property.archived`.

## Request

- **Method**: `DELETE`
- **Path**: `/api/v1/properties/{property}` — `property` = numeric property id
- **Auth**: bearer token
- **Permission**: `3.3`
- **Throttle**: 120 req/min
- **Body**: none

## Responses

### 200 — Success

```json
{
  "data": null
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
