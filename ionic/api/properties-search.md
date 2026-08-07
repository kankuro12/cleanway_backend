# GET /api/v1/properties/search

Quick property search — same filters as the list, but **no pagination** and a **reduced field set** (id, uuid, name, address, formatted_address, lat/lng only). For autocomplete-style search.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/properties/search`
- **Auth**: bearer token
- **Permission**: `3.1`
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `search` | string | no | — | Text search |
| `active` | bool | no | — | Active filter |
| `category_id` | int | no | — | Category filter |
| `tag_id` | int | no | — | Tag filter |
| `geocode_status` | string | no | enum | Geocode filter |
| `missing_coords` | bool | no | — | Missing coords filter |
| `unassigned` | bool | no | — | Unassigned filter |
| `assigned_to` | int | no | — | Assignee filter |
| `limit` | int | no | default 10, **max 50** | Max results |

## Responses

### 200 — Success

```json
{
  "data": [
    {
      "id": 12,
      "uuid": "a1b2c3d4-...",
      "name": "Sky Tower Plaza",
      "address": "100 Victoria St W, Auckland",
      "formatted_address": "100 Victoria Street West, Auckland CBD, Auckland 1010",
      "latitude": -36.8485,
      "longitude": 174.7624
    }
  ]
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- No `meta.pagination`.
- Coerce `limit` to ≤50 client-side to avoid surprises.
