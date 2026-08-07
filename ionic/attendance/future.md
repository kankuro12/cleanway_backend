# Attendance — Future (deferred)

- **Geo-fence push nudge** — server pushes "you're near your shift property, clock in" via FCM; needs backend trigger (scheduled job) — no such job today.
- **Clock-in reminders** — local notification if shift start approaches and no clock-in; trivial app-side, defer until push phase done.
- **Overtime approval flow** — backend has summary only, no overtime-request workflow.
- **Shift swap requests** — no backend concept; would need full feature.
