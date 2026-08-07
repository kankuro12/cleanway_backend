# Notifications

Objective: notification feed mirroring web inbox tabs, FCM push registration, deep links from push taps.

## Screens & structures

1. **Notifications tab** — unread / read segments (mirrors web `?read=0|1`); list: type, title, body, timestamp, read state; unread dots; tap item → mark read + deep link (payload-driven: task/incident id); pull-to-refresh + infinite scroll.
2. **Push handling** — foreground/background tap → route to target screen (task/{id} etc.); cold start from push → deep link after login.
3. **Badge sync** — app icon badge = unread count (from feed meta or last fetch); cleared on read.

## APIs used

`GET /notifications` (`read` 0/1, per_page), `POST /notifications/{id}/read`, `POST /me/devices` (fcm_token, platform android|ios, device_id), `DELETE /me/devices/{token}`.

## Forms

| Form | Fields | Notes |
|---|---|---|
| Device register (silent) | fcm_token, platform, device_id | on login / after push permission grant; upsert by token |
| Device unregister (silent) | fcm_token (path) | on logout |

## Flows

- **Register**: after login, request notification permission → get FCM token → POST device.
- **Receive**: data payload w/ target route + id → navigate; no target → open feed.
- **Read**: tap item → POST read → refetch segment + badge.

## Exit criteria

Push received on both platforms while foreground/background/killed; deep link opens correct screen; unread badge matches feed; logout unregisters device.
