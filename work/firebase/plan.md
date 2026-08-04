# Firebase Notifications

Objective: Firebase Cloud Messaging (FCM) as the in-app/push notification transport. Web app inbox stays the source of truth (`notifications` table); FCM pushes deliver the same notification to registered devices (web + mobile) via queued jobs.

## Scope

1. **SDK** — `kreait/firebase-php` (FCM HTTP v1, service-account auth).
2. **Config** — `config/firebase.php` (`project_id`, `credentials` path, `enabled`, `web_action_url`); `.env` entries `FIREBASE_*`.
3. **Devices** — `user_devices` table (fcm_token unique, platform web|android|ios, device_id, last_seen_at); own-device registration API `POST/DELETE /api/v1/me/devices`.
4. **Messenger** — `app/Services/Notifications/FirebaseMessenger.php` (thin SDK wrapper; no-op + log when unconfigured).
5. **Delivery** — `SendPushNotification` queued job per device token; `notification_deliveries.push` rows: `pending` → `sent|failed`; skipped when user has no devices.
6. **Wiring** — `NotificationService::send(..., channels: [in_app, email, push])`; task assignment already pushes.
7. **Client side** — web: firebase-messaging-sw.js + token capture (separate step; needs FCM web app config), mobile: `messaging().getToken()` in Ionic (later phase).

## Permissions used

None — device registration is own-profile (`1.3` implicitly); all users may receive push for their own notifications.

## API

```
POST   /api/v1/me/devices           { fcm_token, platform?, device_id? }  → upsert + touch
DELETE /api/v1/me/devices/{token}   → remove own device
```

## Exit criteria

- Notification sent → in-app row + email + one FCM push per device.
- Firebase disabled/unconfigured → push silently skipped (delivery row `skipped`), everything else unaffected.
- Token re-registered by another user → ownership moves (upsert by token).
