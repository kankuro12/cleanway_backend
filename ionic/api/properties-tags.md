# GET /api/v1/property-tags

Active property tags, ordered by sort order. Reference data — cache client-side.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/property-tags`
- **Auth**: bearer token
- **Permission**: `3.1`
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data[].id` | int | Tag id |
| `data[].name` | string | Display name |
| `data[].slug` | string | Slug |
| `data[].color` | string | Hex color e.g. `#ff9900` |

```json
{
  "data": [
    {
      "id": 4,
      "name": "High Traffic",
      "slug": "high-traffic",
      "color": "#ff9900"
    },
    {
      "id": 5,
      "name": "After Hours",
      "slug": "after-hours",
      "color": "#4466cc"
    }
  ]
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- No pagination. Cache aggressively (reference data).
