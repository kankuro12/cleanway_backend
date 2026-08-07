# POST /api/v1/attendance/clock-in

Clock in for a shift. Server records the event and evaluates the geofence. **Outside geofence → still recorded, HTTP 202.**

## Request

- **Method**: `POST`
- **Path**: `/api/v1/attendance/clock-in`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields (shared GPS set + extras)

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `latitude` | float | no | −90..90 | Location |
| `longitude` | float | no | −180..180 | Location |
| `gps_accuracy_meters` | int | no | 0..10000 | Accuracy |
| `device_timestamp` | datetime | no | ISO 8601 | Fix time |
| `device_id` | string | no | ≤100 | Device |
| `offline` | bool | no | — | Queued offline event |
| `is_mock_location` | bool | no | — | Mock flag |
| `remarks` | string | no | ≤1000 | Note |
| `shift_id` | int | no | exists shifts | Shift being clocked into (recommended) |

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624,
  "gps_accuracy_meters": 7,
  "device_timestamp": "2026-08-07T21:55:00+00:00",
  "device_id": "android-8f3a",
  "shift_id": 214,
  "remarks": "Starting shift"
}
```

## Responses

### 201 — Created (inside geofence) — Event payload (canonical)

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Event id |
| `data.event_type` | string | `clock_in` |
| `data.server_timestamp` | datetime | Server time (ISO 8601) |
| `data.device_timestamp` | datetime\|null | Device time |
| `data.latitude` | float\|null | Recorded lat |
| `data.longitude` | float\|null | Recorded lng |
| `data.gps_accuracy_meters` | int\|null | Accuracy |
| `data.effective_radius_meters` | int\|null | Radius applied |
| `data.distance_from_property_meters` | float\|null | Distance to property |
| `data.inside_geofence` | bool | Geofence verdict |
| `data.offline` | bool | Was queued offline |
| `data.integrity_flags` | array\|null | Integrity warnings |
| `data.exception` | object\|null | `{id, policy, reason}` or null |

```json
{
  "data": {
    "id": 4120,
    "event_type": "clock_in",
    "server_timestamp": "2026-08-07T21:55:12+00:00",
    "device_timestamp": "2026-08-07T21:55:00+00:00",
    "latitude": -36.8485,
    "longitude": 174.7624,
    "gps_accuracy_meters": 7,
    "effective_radius_meters": 120,
    "distance_from_property_meters": 12.4,
    "inside_geofence": true,
    "offline": false,
    "integrity_flags": [],
    "exception": null
  }
}
```

### 202 — Outside geofence (still recorded)

Same event payload with `inside_geofence: false` and an `exception` describing the violation.

```json
{
  "data": {
    "id": 4121,
    "event_type": "clock_in",
    "server_timestamp": "2026-08-07T21:56:00+00:00",
    "device_timestamp": "2026-08-07T21:55:30+00:00",
    "latitude": -36.8300,
    "longitude": 174.7400,
    "gps_accuracy_meters": 9,
    "effective_radius_meters": 120,
    "distance_from_property_meters": 2400.0,
    "inside_geofence": false,
    "offline": false,
    "integrity_flags": ["outside_geofence"],
    "exception": {
      "id": 80,
      "policy": "outside_geofence",
      "reason": "You are 2.4 km from the shift property."
    }
  }
}
```

### 422 — Validation

```json
{
  "message": "The selected shift_id is invalid.",
  "errors": {
    "shift_id": ["The selected shift_id is invalid."]
  }
}
```

### 401 / 429

Standard envelopes.

## Notes

- Treat 201 and 202 both as success — the event was recorded either way; show the geofence state to the user.
- `event_type` on all clock endpoints: `clock_in`, `break_start`, `break_end`, `clock_out`.
