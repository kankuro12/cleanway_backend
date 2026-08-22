# POST /api/v1/properties

Create a property. Geocoding runs async server-side (status via `geocode_status` on subsequent reads).

## Request

- **Method**: `POST`
- **Path**: `/api/v1/properties`
- **Auth**: bearer token
- **Permission**: `3.2`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `name` | string | yes | ≤255 | Property name |
| `address` | string | yes | ≤500 | Address |
| `formatted_address` | string | no | ≤500 | Formatted address |
| `google_place_id` | string | no | ≤255 | Google Places id |
| `latitude` | float | no | −90..90 | Coordinates (geocoded if absent) |
| `longitude` | float | no | −180..180 | Coordinates |
| `property_category_id` | int | no | exists property_categories | Category |
| `contact_name` | string | no | ≤255 | Contact |
| `contact_phone` | string | no | ≤50 | Contact phone |
| `contact_email` | string | no | valid email, ≤255 | Contact email |
| `postal_code` | string | no | ≤20 | Postal code |
| `access_instructions` | string | no | — | Access notes |
| `parking_instructions` | string | no | — | Parking notes |
| `safety_instructions` | string | no | — | Safety notes |
| `special_cleaning_requirements` | string | no | — | Special requirements |
| `service_frequency` | string | no | ≤30 | e.g. `daily` |
| `permitted_check_in_radius_meters` | int | no | 0..100000 | Geofence radius |
| `active` | bool | no | — | Defaults on |
| `internal_notes` | string | no | — | Internal notes |
| `tags` | array | no | each: int, exists property_tags | Tag ids |
| `cleaning_duration_hours` | int | no | 0..240 | Time needed to clean (hours) |
| `cleaning_duration_minutes` | int | no | 0..59 | Time needed to clean (minutes) |
| `client_fixed_amount` | float | no | ≥0 | Fixed amount charged to client |
| `cleaner_pay_type` | string | no | `fixed` or `per_hour` | How the cleaner is paid |
| `cleaner_fixed_amount` | float | no | ≥0 | Fixed cleaner payout (when `fixed`) |
| `cleaner_rate_per_hour` | float | no | ≥0 | Cleaner rate (when `per_hour`) |
| `parking_fee` | float | no | ≥0 | Parking money company pays the cleaner |
| `needs_parking` | bool | no | — | If property needs parking (shows fee) |
| `latitude`/`longitude` | float | no | — | Use Leaflet pin or Nominatim geocode from address |

### Request example

```json
{
  "name": "Harbour View Offices",
  "address": "12 Quay St, Auckland",
  "property_category_id": 2,
  "contact_name": "Sam",
  "contact_phone": "0215551111",
  "postal_code": "1010",
  "permitted_check_in_radius_meters": 100,
  "tags": [4, 5],
  "cleaning_duration_hours": 1,
  "cleaning_duration_minutes": 30,
  "client_fixed_amount": 120,
  "cleaner_pay_type": "per_hour",
  "cleaner_rate_per_hour": 45,
  "parking_fee": 8,
  "needs_parking": true,
  "latitude": -36.8485,
  "longitude": 174.7633
}
```

## Responses

### 201 — Created

Full PropertyResource (fields in [properties-show](properties-show.md)). `geocode_status` initially `pending`.

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
    "geocode_status": "pending",
    "geocoded_at": null,
    "location_source": null,
    "permitted_check_in_radius_meters": 100,
    "property_category_id": 2,
    "category": { "id": 2, "name": "Commercial" },
    "contact_name": "Sam",
    "contact_phone": "0215551111",
    "contact_email": null,
    "postal_code": "1010",
    "access_instructions": null,
    "parking_instructions": null,
    "safety_instructions": null,
    "service_frequency": null,
    "cleaning_duration_minutes": 90,
    "client_fixed_amount": 120.0,
    "cleaner_pay_type": "per_hour",
    "cleaner_fixed_amount": null,
    "cleaner_rate_per_hour": 45.0,
    "parking_fee": 8.0,
    "needs_parking": true,
    "latitude": -36.8485,
    "longitude": 174.7633,
    "active": true,
    "tags": [
      { "id": 4, "name": "High Traffic", "color": "#ff9900" },
      { "id": 5, "name": "After Hours", "color": "#4466cc" }
    ],
    "assignments": [],
    "created_at": "2026-08-07T00:00:00+00:00",
    "updated_at": "2026-08-07T00:00:00+00:00"
  }
}
```

### 422 — Validation

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

### 401 / 403 / 429

Standard envelopes.
