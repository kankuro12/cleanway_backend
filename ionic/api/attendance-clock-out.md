# POST /api/v1/attendance/clock-out

Clock out. Always 201 (geofence does not gate the status code).

## Request

- **Method**: `POST`
- **Path**: `/api/v1/attendance/clock-out`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

Shared GPS set + `remarks` (≤1000). See [attendance-clock-in](attendance-clock-in.md).

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624,
  "gps_accuracy_meters": 8,
  "device_timestamp": "2026-08-08T02:00:00+00:00",
  "device_id": "android-8f3a",
  "remarks": "Shift complete"
}
```

## Responses

### 201 — Created

Event payload with `event_type: "clock_out"` (canonical table in [attendance-clock-in](attendance-clock-in.md)).

```json
{
  "data": {
    "id": 4140,
    "event_type": "clock_out",
    "server_timestamp": "2026-08-08T02:00:02+00:00",
    "device_timestamp": "2026-08-08T02:00:00+00:00",
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

- Clock-out without a prior clock-in is rejected by the server state machine — expect 422 with a message.
