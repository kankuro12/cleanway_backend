# POST /api/v1/tasks/{task}/check-out

GPS check-out from a task. Identical contract to check-in.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}/check-out` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

Shared GPS set — see [tasks-check-in](tasks-check-in.md): `latitude`, `longitude`, `gps_accuracy_meters`, `device_timestamp`, `device_id`, `offline`, `is_mock_location`.

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624,
  "gps_accuracy_meters": 6,
  "device_timestamp": "2026-08-08T02:00:00+00:00",
  "device_id": "android-8f3a"
}
```

## Responses

### 200 — Success (inside geofence)

```json
{
  "data": {
    "event_id": 4108,
    "inside_geofence": true,
    "blocked": false,
    "exception": null,
    "task_status": "in_progress"
  }
}
```

### 403 — Blocked

```json
{
  "data": {
    "event_id": 4109,
    "inside_geofence": false,
    "blocked": true,
    "exception": {
      "id": 78,
      "policy": "outside_geofence",
      "reason": "You are 800 m from the property."
    },
    "task_status": "in_progress"
  }
}
```

### 422 / 404 / 401 / 429

Standard envelopes.
