# POST /api/v1/properties/{property}/retry-geocode

Re-run geocoding, or set coordinates manually (manual override geocodes from given lat/lng). Returns updated property.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/properties/{property}/retry-geocode` — `property` = numeric property id
- **Auth**: bearer token
- **Permission**: `3.3`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `latitude` | float | no | −90..90 | Manual coordinates |
| `longitude` | float | no | −180..180 | Manual coordinates |

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624
}
```

## Responses

### 200 — Success

Full PropertyResource (fields in [properties-show](properties-show.md)) — `geocode_status` reflects the new state.

```json
{
  "data": {
    "id": 99,
    "name": "Harbour View Offices",
    "address": "12 Quay St, Auckland",
    "latitude": -36.8485,
    "longitude": 174.7624,
    "geocode_status": "manually_adjusted",
    "geocoded_at": "2026-08-07T02:00:00+00:00",
    "location_source": "manual",
    "active": true,
    "tags": [],
    "assignments": [],
    "created_at": "2026-08-07T00:00:00+00:00",
    "updated_at": "2026-08-07T02:00:00+00:00"
  }
}
```

### 404 / 401 / 403 / 429

Standard envelopes.
