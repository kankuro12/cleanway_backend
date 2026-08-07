# GET /api/v1/properties

Paginated property list with filters. Eager-loads `category` and `tags`.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/properties`
- **Auth**: bearer token
- **Permission**: `3.1`
- **Throttle**: 120 req/min

### Query params

| Param | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `search` | string | no | — | Text search (name/address) |
| `active` | bool | no | `true`/`false` | Filter by active flag |
| `category_id` | int | no | exists | Filter by category |
| `tag_id` | int | no | exists | Filter by tag |
| `geocode_status` | string | no | `pending`\|`resolved`\|`manually_adjusted`\|`failed` | Filter by geocode state |
| `missing_coords` | bool | no | `true` | Only properties without coordinates |
| `unassigned` | bool | no | `true` | Only unassigned properties |
| `assigned_to` | int | no | — | Assignee id (user or team) |
| `per_page` | int | no | default 25 | Page size |

## Responses

### 200 — Success

`data` = array of PropertyResource — full field list in [properties-show](properties-show.md).

```json
{
  "data": [
    {
      "id": 12,
      "uuid": "a1b2c3d4-...",
      "name": "Sky Tower Plaza",
      "address": "100 Victoria St W, Auckland",
      "formatted_address": "100 Victoria Street West, Auckland CBD, Auckland 1010",
      "google_place_id": "ChIJ...",
      "latitude": -36.8485,
      "longitude": 174.7624,
      "geocode_accuracy": "rooftop",
      "geocode_status": "resolved",
      "geocoded_at": "2025-02-01T10:00:00+00:00",
      "location_source": "google_places",
      "permitted_check_in_radius_meters": 120,
      "property_category_id": 2,
      "category": { "id": 2, "name": "Commercial" },
      "contact_name": "Jane Doe",
      "contact_phone": "0215550000",
      "contact_email": "jane@skyplaza.co.nz",
      "postal_code": "1010",
      "access_instructions": "Use loading dock entrance",
      "parking_instructions": "Visitor bays level 2",
      "safety_instructions": null,
      "service_frequency": "daily",
      "active": true,
      "tags": [{ "id": 4, "name": "High Traffic", "color": "#ff9900" }],
      "assignments": [],
      "created_at": "2025-02-01T09:00:00+00:00",
      "updated_at": "2025-02-01T10:00:00+00:00"
    }
  ],
  "meta": {
    "pagination": {
      "total": 87,
      "per_page": 25,
      "current_page": 1,
      "last_page": 4
    }
  }
}
```

### 401 / 403 / 429

Standard envelopes.

## Notes

- Ordered by `updated_at` descending.
- `assignments` is only populated when the relation is loaded — on the list endpoint it is **absent** (not `null`). Handle missing keys.
