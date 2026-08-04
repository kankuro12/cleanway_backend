# Firebase — Suggestions

- Keep `FIREBASE_ENABLED=false` in local dev; enable in staging/prod with a real service account.
- Register the web token on every page load (`me/devices` upsert) so re-login keeps devices fresh; touch `last_seen_at` to spot dead tokens.
- Log FCM failures at `warning` with the delivery id — debugging dead tokens is otherwise silent.
- Add `FIREBASE_WEB_ACTION_URL` per environment so push taps land on the right console URL.
- Do not block notification writes on FCM latency: delivery rows are written first, jobs update them async.
