# Offline & Hardening — Future (deferred)

- **SQLite/WatermelonDB store** — only when queue volume or read-cache scale outgrows Preferences (measure first).
- **Background location/geofence** — server-side geofence nudge needs backend trigger; foreground location is enough for v1.
- **Automatic sync conflicts UI** — three-way merge for edited-since-queued records; today server wins + message (acceptable: writes are single-user).
- **Automated device testing** (Maestro) — after manual matrix stabilizes.
- **App store automation** (Fastlane) — when releases become frequent.
