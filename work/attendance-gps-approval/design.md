# Attendance, GPS & Approval — Design

## ER

```
shifts ─< attendance_events (immutable) >── properties (nullable)
         └ attendance_correction_requests: original_event_id, requested_at, reason, decision, decided_by
tasks ─< task_evidence
tasks ─< task_approvals
tasks ─< incidents ─< incident_evidence
gps_exceptions: event_id, policy (accept|exception|override|reject), integrity_flags json, resolved_at
```

## Check-in flow (spec §12.1)

1. Device sends lat/lng/accuracy/timestamp.
2. Accuracy > threshold → warn/reject (config).
3. Distance vs property coords (geodesic).
4. Resolve effective radius (property → category → org → system).
5. Store distance + radius + inside_geofence on event.
6. Policy: accept | accept+exception | require override | reject → `gps_exceptions` row when exception.

## Attendance correction (spec §11.3)

Original event untouched → new `manual_correction` event + `attendance_correction_requests` → supervisor approve/reject → audited.

## Completion gate (spec §13.2)

`CompleteTask` validates: required checklist answers (snapshot), min before/after photos, required remarks, GPS check-out, incident ack → else validation error listing missing items.

## Key services

- `app/Domain/Attendance/RecordAttendanceEvent.php`
- `app/Domain/Attendance/SubmitAttendanceCorrection.php`
- `app/Domain/Tasks/CheckInToTask.php` / `CheckOutFromTask.php`
- `app/Domain/Tasks/CompleteTask.php` / `SubmitTaskForApproval.php`
- `app/Domain/Tasks/ApproveTask.php` / `RejectTask.php` / `RequestTaskCorrection.php` / `ReopenTask.php`
- `app/Domain/Tasks/UploadTaskEvidence.php`
- `app/Domain/Incidents/RaiseIncident.php` / `ResolveIncident.php`
- `app/Support/Geodesic.php`, `app/Services/Geocoding/EffectiveRadiusResolver.php`
- `app/Services/Attendance/AttendanceRules.php`

## Tests

- E2E: create → assign → check-in (in radius) → start → checklist → photos → remarks → check-out → submit → approve (spec §24.4) + failure variants (geocode fail, offline, out-of-radius, missing photo, correction requested).
- Radius fallback order; corrections immutability; approval rules incl. own-task block.