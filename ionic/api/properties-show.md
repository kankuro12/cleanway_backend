# GET /api/v1/properties/{property}

Full property detail. Loads `category`, `tags`, and `assignments.assignable`.

## Request

- **Method**: `GET`
- **Path**: `/api/v1/properties/{property}` — `property` = numeric property id
- **Auth**: bearer token
- **Permission**: `3.1`
- **Throttle**: 120 req/min
- **Query params**: none

## Responses

### 200 — Success — PropertyResource (canonical field list)

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Property id |
| `data.uuid` | string | UUID |
| `data.name` | string | Name |
| `data.address` | string | Address as entered |
| `data.formatted_address` | string\|null | Google-formatted address |
| `data.google_place_id` | string\|null | Google Places id |
| `data.latitude` | float\|null | Latitude |
| `data.longitude` | float\|null | Longitude |
| `data.geocode_accuracy` | string\|null | Geocode accuracy level |
| `data.geocode_status` | string | `pending`, `resolved`, `manually_adjusted`, `failed` |
| `data.geocoded_at` | datetime\|null | ISO 8601 |
| `data.location_source` | string\|null | e.g. `google_places`, `manual` |
| `data.permitted_check_in_radius_meters` | int\|null | Geofence radius |
| `data.property_category_id` | int\|null | Category id |
| `data.category` | object\|null | `{"id": int, "name": string}` |
| `data.contact_name` | string\|null | Contact person |
| `data.contact_phone` | string\|null | Contact phone |
| `data.contact_email` | string\|null | Contact email |
| `data.postal_code` | string\|null | Postal code |
| `data.access_instructions` | string\|null | Access notes |
| `data.parking_instructions` | string\|null | Parking notes |
| `data.safety_instructions` | string\|null | Safety notes |
| `data.service_frequency` | string\|null | e.g. `daily` |
| `data.active` | bool | Is active |
| `data.tags` | array | `[{id, name, color}]` |
| `data.assignments` | array | See below |
| `data.created_at` | datetime | ISO 8601 |
| `data.updated_at` | datetime | ISO 8601 |

`assignments[]` fields: `id` (int), `assignable_type` (string), `assignable_id` (int), `assignment_role` (string), `start_date` (date\|null), `end_date` (date\|null), `is_primary` (bool), `assignable` (`{"id", "name"}` or null).

```json
{
  "data": {
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
    "assignments": [
      {
        "id": 31,
        "assignable_type": "App\\Models\\User",
        "assignable_id": 6,
        "assignment_role": "primary_cleaner",
        "start_date": "2025-02-01",
        "end_date": null,
        "is_primary": true,
        "assignable": { "id": 6, "name": "Jordan Cleaner" }
      }
    ],
    "created_at": "2025-02-01T09:00:00+00:00",
    "updated_at": "2025-02-01T10:00:00+00:00"
  }
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
