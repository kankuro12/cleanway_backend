# POST /api/v1/attendance/break/end

End the current break. Always 201.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/attendance/break/end`
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
  "device_timestamp": "2026-08-07T23:45:00+00:00",
  "device_id": "android-8f3a"
}
```

## Responses

### 201 — Created

Event payload with `event_type: "break_end"` (canonical table in [attendance-clock-in](attendance-clock-in.md)).

```json
{
  "data": {
    "id": 4131,
    "event_type": "break_end",
    "server_timestamp": "2026-08-07T23:45:03+00:00",
    "device_timestamp": "2026-08-07T23:45:00+00:00",
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

- Ending a break without a running break is rejected by the server's event state machine — expect a 422 with a message.
