# POST /api/v1/me/devices

Register (or refresh) an FCM push device token for the current user. Upsert keyed by `fcm_token` — re-POST the same token to refresh platform/device info.

## Request

- **Method**: `POST`
- **Path**: `/api/v1/me/devices`
- **Auth**: bearer token
- **Permission**: any authenticated user
- **Throttle**: 120 req/min
- **Content-Type**: `application/json`

### Body fields

| Field | Type | Required | Constraints | Description |
|---|---|---|---|---|
| `fcm_token` | string | yes | ≤512 | Firebase Messaging token |
| `platform` | string | no | `web` \| `android` \| `ios` (default `web`) | Platform |
| `device_id` | string | no | ≤100 | Device identifier |

### Request example

```json
{
  "fcm_token": "dQw4w9WgXcQ:APA91b...",
  "platform": "android",
  "device_id": "android-8f3a"
}
```

## Responses

### 201 — Created / Updated

| Field | Type | Description |
|---|---|---|
| `data.id` | int | Device record id |
| `data.platform` | string | Platform stored |

```json
{
  "data": {
    "id": 44,
    "platform": "android"
  }
}
```

### 422 — Validation

```json
{
  "message": "The fcm_token field is required.",
  "errors": {
    "fcm_token": ["The fcm_token field is required."]
  }
}
```

### 401 / 429

Standard envelopes.

## Notes

- Call after login / after obtaining a fresh FCM token; call destroy on logout.
- Same `fcm_token` posted again simply updates the record (201 both times).
