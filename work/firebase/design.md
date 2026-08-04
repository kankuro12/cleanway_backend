# Firebase — Design

## Flow

```
NotificationService::send(user, type, title, body, payload, key, channels)
  ├─ in_app → notifications row (inbox)
  ├─ email  → queued Mailable (TaskAssignedMail…)  + delivery row sent
  └─ push   → user.devices (0..n)
       ├─ none → delivery row skipped
       └─ each → delivery row pending + SendPushNotification job (token, title, body, payload)
                    └─ FirebaseMessenger.send() → sent | failed (row updated, tries=3)
```

## ER

```
users ─< user_devices (fcm_token unique, platform, device_id, last_seen_at)
notifications ─< notification_deliveries (channel push, status pending|sent|failed|skipped)
```

## Auth

FCM HTTP v1 uses the service account (Google Auth SDK, handled by kreait/firebase-php). The credentials JSON lives outside the repo (env path) — never committed.

## Failure behaviour

- `FIREBASE_ENABLED=false` or missing credentials → `FirebaseMessenger` returns false, delivery row `failed` after job, or `skipped` before dispatch; app flow unaffected.
- Invalid/expired device token → job fails, row `failed` (3 attempts). Device pruning on repeated `InvalidArgument` (UNREGISTERED) is a future task.
