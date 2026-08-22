# PUT /api/v1/properties/{property}

Update a property. **Send the full object** — most fields are required in the payload rules (partial update not supported). Logs an audit entry `property.updated`.

## Request

- **Method**: `PUT`
- **Path**: `/api/v1/properties/{property}` — `property` = numeric property id
- **Auth**: bearer token
- **Permission**: `3.3`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

Same as [properties-store](properties-store.md) (full set), plus:

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `geocode_status` | string | no | `pending`\|`resolved`\|`manually_adjusted`\|`failed` | Manual geocode state |

### Request example

```json
{
  "name": "Harbour View Offices",
  "address": "12 Quay St, Auckland",
  "contact_phone": "0215552222",
  "permitted_check_in_radius_meters": 150,
  "tags": [4],
  "geocode_status": "manually_adjusted",
  "cleaning_duration_hours": 1,
  "cleaning_duration_minutes": 30,
  "client_fixed_amount": 120,
  "cleaner_pay_type": "per_hour",
  "cleaner_rate_per_hour": 45,
  "parking_fee": 8,
  "needs_parking": true
}
```

## Responses

### 200 — Success

Full PropertyResource (fields in [properties-show](properties-show.md)).

```json
{
  "data": {
    "id": 99,
    "uuid": "b2c3d4e5-...",
    "name": "Harbour View Offices",
    "address": "12 Quay St, Auckland",
    "formatted_address": null,
    "google_place_id": null,
    "latitude": null,
    "longitude": null,
    "geocode_accuracy": null,
    "geocode_status": "manually_adjusted",
    "geocoded_at": null,
    "location_source": null,
    "permitted_check_in_radius_meters": 150,
    "property_category_id": 2,
    "category": { "id": 2, "name": "Commercial" },
    "contact_name": "Sam",
    "contact_phone": "0215552222",
    "contact_email": null,
    "postal_code": "1010",
    "access_instructions": null,
    "parking_instructions": null,
    "safety_instructions": null,
    "service_frequency": null,
    "active": true,
    "tags": [{ "id": 4, "name": "High Traffic", "color": "#ff9900" }],
    "assignments": [],
    "created_at": "2026-08-07T00:00:00+00:00",
    "updated_at": "2026-08-07T01:00:00+00:00"
  }
}
```

### 422 / 404 / 401 / 403 / 429

Standard envelopes.
