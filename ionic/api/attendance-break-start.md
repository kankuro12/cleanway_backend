# POST /api/v1/attendance/break/start

Start a break. Contract mirrors clock-in (event payload), but geofence violations do **not** change the status code — always 201.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/attendance/break/start`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

Shared GPS set + `remarks` (≤1000). See [attendance-clock-in](attendance-clock-in.md) for the field table. `shift_id` accepted (validation allows it) though primarily used on clock-in.

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624,
  "gps_accuracy_meters": 8,
  "device_timestamp": "2026-08-07T23:30:00+00:00",
  "device_id": "android-8f3a"
}
```

## Responses

### 201 — Created

Event payload (canonical table in [attendance-clock-in](attendance-clock-in.md)) with `event_type: "break_start"`.

```json
{
  "data": {
    "id": 4130,
    "event_type": "break_start",
    "server_timestamp": "2026-08-07T23:30:05+00:00",
    "device_timestamp": "2026-08-07T23:30:00+00:00",
    "latitude": -36.8485,
    "longitude": 174.7624,
    "gps_accuracy_meters": 8,
    "effective_radius_meters": 120,
    "distance_from_property_meters": 9.1,
    "inside_geofence": true,
    "offline": false,
    "integrity_flags": [],
    "exception": null
  }
}
```

### 422 / 401 / 429

Standard envelopes.

## Notes

- Server tracks break pairing; `break_end` after `break_start` computes break minutes.
