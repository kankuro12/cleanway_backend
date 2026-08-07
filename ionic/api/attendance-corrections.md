# POST /api/v1/attendance/corrections

Request a correction for **one of your own** attendance events (e.g. missed clock-out). Decision handled by supervisor/admin out-of-band.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/attendance/corrections`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `original_event_id` | int | yes | exists attendance_events | The event to correct |
| `reason` | string | yes | ≤1000 | Why the correction is needed |

### Request example

```json
{
  "original_event_id": 4140,
  "reason": "Phone died before I could clock out."
}
```

## Responses

### 201 — Created

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Correction request id |
| `data.decision` | string | `pending` \| `approved` \| `rejected` (initially `pending`) |

```json
{
  "data": {
    "id": 61,
    "decision": "pending"
  }
}
```

### 403 — Not your event

```json
{
  "message": "You can only correct your own attendance events."
}
```

### 422 — Validation

```json
{
  "message": "The original_event_id field is required.",
  "errors": {
    "original_event_id": ["The original_event_id field is required."]
  }
}
```

### 401 / 429

Standard envelopes.

## Notes

- Only the event owner can request a correction (403 otherwise).
- No list endpoint for corrections exists yet — keep the returned id locally to track status (backend addition planned; see `ionic/plan.md`).
