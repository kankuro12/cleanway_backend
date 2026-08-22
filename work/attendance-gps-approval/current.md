# Attendance, GPS & Approval — Current

## Done

- [x] Migrations: `shifts`, `attendance_events` (immutable), `attendance_correction_requests`, `gps_exceptions`, `task_checklist_responses`, `task_evidence`, `task_approvals`, `incidents`, `incident_evidence`.
- [x] Models with status constants; `AttendanceEvent` blocks update/delete (LogicException).
- [x] `Geodesic` haversine support class; `config/gps.php` (accuracy threshold, out-of-radius policy, missing-coords policy, completion gate flags).
- [x] `RecordAttendanceEvent`: event types, server/device timestamps, distance + effective radius + inside_geofence, integrity flags (low accuracy, mock, device time diff, offline), geofence policy → `gps_exceptions` (accept/exception/override/reject), missing-coordinates path without crash.
- [x] `AttendanceRules`: late/early/missed/overtime/work/break durations; shift summaries.
- [x] `SubmitAttendanceCorrection`: request + approve/reject; approval writes new `manual_correction` event, original untouched.
- [x] `CheckInToTask`/`CheckOutFromTask`: GPS-gated, task snapshot coordinates + radius, policy blocking (override/reject), auto accept/start side-effects.
- [x] `CompleteTask`: completion gate (required checklist responses, min before/after photos from type snapshot, remarks, GPS check-out, incident ack) with structured missing-item errors.
- [x] `TaskApprovalActions`: approve/reject/request_correction/reopen → `task_approvals` row + state machine transition; no self-approval inherited.
- [x] `UploadTaskEvidence` + `ProcessEvidenceImage` job (checksum, dims, ready) + `evidence` storage disk.
- [x] `RaiseIncident` + status transitions + evidence upload.
- [x] Web: shifts board (+create/status), attendance event log, corrections queue, approval queue, incidents register + raise form, evidence upload on task edit.
- [x] API: clock-in/break start/end/clock-out, `/me/shifts` (with summaries), correction request, task check-in/check-out/evidence/complete/incidents, all behind permission gates.
- [x] Standalone Office Attendance Geofencing: `branches` geofence columns (`latitude`, `longitude`, `geofence_radius_meters`), task-free office punch-in/out in `RecordAttendanceEvent`, `cleaner-tools` interactive web console with HTML5 location fetch & distance calculator.
- [x] Shift Report: Web view (`/admin/reports/shifts`) & API endpoint (`/api/v1/reports/shifts`) with KPI summary metrics, filters, and detailed shift punch logs with geofence badges.
- [x] Typography: Configured Google Fonts `Roboto` across design system in `tokens.css` and `app.blade.php`.
- [x] Tests: Added `OfficeAttendanceAndShiftReportTest` (82 total tests passing).

## Verified

- 82 tests green (79 prior + 3 new), all assertions passed.
- E2E workflow (spec §24.4) passes; invalid GPS states rejected; exceptions reviewable.

## Next

1. Reports phase: dashboard widgets, exports, audit viewer, settings.
2. GPS exception resolution UI (manager approve/deny) — resolution action exists in schema, UI deferred.
3. Real image compression/thumbnails in `ProcessEvidenceImage`.
