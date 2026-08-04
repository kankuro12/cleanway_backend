# Firebase — Current

## Done

- [x] `kreait/firebase-php` v8.3 installed.
- [x] `config/firebase.php` (project_id, credentials, enabled, web_action_url) + `.env.example` entries.
- [x] Migration `user_devices` (fcm_token unique, platform, device_id, last_seen_at) + `UserDevice` model + `User::devices()`.
- [x] `FirebaseMessenger` service — FCM HTTP v1 via SDK, lazy init, graceful no-op/log when disabled or credentials missing.
- [x] `SendPushNotification` queued job (tries 3) — marks `notification_deliveries.push` sent/failed.
- [x] `NotificationService`: `push` channel — one delivery row + job per device; `skipped` when no devices; `pending` → job result.
- [x] API: `POST /api/v1/me/devices` (upsert by token, ownership moves on re-register), `DELETE /api/v1/me/devices/{token}`.
- [x] Task assignment notifications now fan out to `in_app + email + push`.
- [x] Tests: 6 (`FirebasePushTest`) — registration upsert/delete, per-device job dispatch, no-device skip, job sent/failed paths, messenger no-op when unconfigured.

## Verified

- 108 tests green (102 prior + 6 new), pint clean.
- Pipeline never depends on a working Firebase account: unconfigured → `skipped`, app healthy.

## Next

1. Web client: `firebase-messaging-sw.js` service worker + `firebase/app` init + token capture → `POST /me/devices` (needs FCM web app config + `FIREBASE_*_PUBLIC` env for JS).
2. Mobile (Ionic phase): `messaging().getToken()` + background handler.
3. Multi-project/tenant config if a second Firebase project is needed.
4. Delivery webhook / status reconciliation (FCM delivery reports).
