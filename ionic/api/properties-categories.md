# GET /api/v1/property-categories

Active property categories, ordered by sort order. Reference data — cache client-side.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/property-categories`
- **Auth**: bearer token
- **Permission**: `3.1`
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success

| Field | Type | Description |
|---|---|---|
| `data[].id` | int | Category id |
| `data[].name` | string | Display name |
| `data[].slug` | string | Slug |
| `data[].default_check_in_radius_meters` | int\|null | Default geofence radius |

```json
{
  "data": [
    {
      "id": 1,
      "name": "Retail",
      "slug": "retail",
      "default_check_in_radius_meters": 100
    },
    {
      "id": 2,
      "name": "Commercial",
      "slug": "commercial",
      "default_check_in_radius_meters": 120
    }
  ]
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- No pagination. Cache aggressively (reference data).
