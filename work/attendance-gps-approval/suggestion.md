# Attendance, GPS & Approval — Suggestions

- **Immutable events**: `attendance_events` never updated after insert; corrections append new event + link original (spec §11.3) — prevents audit drift.
- **Effective radius snapshot**: store resolved radius + distance + inside_geofence on each event so config changes never rewrite history (spec §2.3).
- **Geodesic distance**: haversine in `app/Support/Geodesic.php` — single util used by check-in, exception logic, nearby search.
- **Policy as config**: `config/gps.php` (accuracy threshold, default radius, exception policy, integrity flags) — cached, no code change per org.
- **Evidence checksum**: hash file on upload; dedupe + verify on completion.
- **Approval immutability**: task_approvals append-only; `previous_approval_state` preserved.
- **Own-task guard**: central rule "cleaner cannot approve own task" in approval action + test — not in controller.
- **Queued processing**: image resize/thumbnail/geocode retries via jobs (spec §0.11).
- **Offline flag**: accept `device_timestamp` + `offline=true` from API, process with delayed sync markers; idempotency key on event uuid.