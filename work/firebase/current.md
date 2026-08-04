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
- [x] Web client: `partials/firebase.blade.php` (firebase-app + messaging ESM init, `onMessage` foreground toast, SW registration, permission request, token → `POST /admin/devices`), `public/firebase-messaging-sw.js` (background handler + notification click → `/admin/notifications`), web config via `FIREBASE_WEB_*` env, web device route `POST /admin/devices` (auth + CSRF, reuses `Api\V1\DeviceController`).
- [x] Admin credential wired: `FIREBASE_CREDENTIALS=storage/app/private/firebase.json` + `storage/app/private` added to `.gitignore` (was committable!).
- [x] Tests: 8 (`FirebasePushTest`) — registration upsert/delete, per-device job dispatch, no-device skip, job sent/failed paths, messenger no-op when unconfigured, web route auth + upsert, `configured()` with real credential file.

## Verified

- 115 tests green, pint clean.
- Live FCM send verified against `test-cc2e6` (browser device token → delivery success, toast shown).
- Pipeline never depends on a working Firebase account: unconfigured → `skipped`, app healthy.
- `.env` holds the live web config (public values) + admin credential path (secret, gitignored).

## Bugs fixed during live verification

- **Jobs never ran**: `QUEUE_CONNECTION=database` with no worker → deliveries stayed `pending` forever, no FCM call. Dev must run `php84 artisan queue:work` (or `queue:listen`).
- **FCM data must be string-only**: int/bool payload values (`task_id: 1`, `test: true`) threw `MessageData::isBinary()`. `FirebaseMessenger` now casts scalars to strings.
- **kreait v8 API**: `Factory::create()` removed — use `new Factory`; `createMessaging()` returns `Kreait\Firebase\Contract\Messaging` interface, not the concrete class (property type fixed).

## Next

1. Set `FIREBASE_VAPID_KEY` (Firebase console → Cloud Messaging → Web Push certificate) — without it the browser token call is skipped (warning in console).
2. Mobile (Ionic phase): `messaging().getToken()` + background handler.
3. Multi-project/tenant config if a second Firebase project is needed.
4. Delivery webhook / status reconciliation (FCM delivery reports).
