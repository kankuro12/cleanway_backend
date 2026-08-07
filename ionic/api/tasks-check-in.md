# POST /api/v1/tasks/{task}/check-in

GPS check-in to a task. Server evaluates the geofence against the task's property radius. Same shape for check-out.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/tasks/{task}/check-in` — `task` = numeric task id
- **Auth**: bearer token
- **Permission**: `4.4`
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields (shared GPS set)

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `latitude` | float | no | −90..90 | Location |
| `longitude` | float | no | −180..180 | Location |
| `gps_accuracy_meters` | int | no | 0..10000 | Reported accuracy |
| `device_timestamp` | datetime | no | ISO 8601 | When the fix was taken |
| `device_id` | string | no | ≤100 | Stable device identifier |
| `offline` | bool | no | — | True when syncing a queued offline event |
| `is_mock_location` | bool | no | — | OS-reported mock flag |

### Request example

```json
{
  "latitude": -36.8485,
  "longitude": 174.7624,
  "gps_accuracy_meters": 8,
  "device_timestamp": "2026-08-07T22:01:00+00:00",
  "device_id": "android-8f3a",
  "is_mock_location": false
}
```

## Responses

### 200 — Success (inside geofence)

| Field | Type | Description |
|---|---|---|
| `data.event_id` | int | Attendance/GPS event id |
| `data.inside_geofence` | bool | `true` |
| `data.blocked` | bool | `false` |
| `data.exception` | object\|null | `null` |
| `data.task_status` | string | Task status after check-in (e.g. `in_progress`) |

```json
{
  "data": {
    "event_id": 4102,
    "inside_geofence": true,
    "blocked": false,
    "exception": null,
    "task_status": "in_progress"
  }
}
```

### 403 — Blocked (geofence policy)

Same shape with `blocked: true`; `exception` describes the policy violation; `task_status` unchanged.

```json
{
  "data": {
    "event_id": 4103,
    "inside_geofence": false,
    "blocked": true,
    "exception": {
      "id": 77,
      "policy": "outside_geofence",
      "reason": "You are 1.4 km from the property."
    },
    "task_status": "assigned"
  }
}
```

### 422 / 404 / 401 / 429

Standard envelopes.

## Notes

- A blocked check-in does **not** change task status — show the `exception.reason` to the user.
- `task_status` reflects the task after the call; use it to refresh the UI.
