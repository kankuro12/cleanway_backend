# Firebase — Future

- Web service worker (`firebase-messaging-sw.js`) + browser token registration.
- Ionic push handling (permission prompt, foreground/background messages, deep-link to task/notification).
- Device pruning: delete `user_devices` rows whose tokens come back `UNREGISTERED` from FCM.
- Notification preference gating per channel (`notification_preferences` on User) — mandatory operational notifications always sent.
- Quiet hours (suppress non-operational push).
- FCM analytics/webhook for delivery + impression stats.
- Topic-based push (per team/branch) for broadcast messages.
