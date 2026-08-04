# Attendance, GPS & Approval

Objective: shifts, attendance events (immutable), GPS/geofence validation, evidence, completion enforcement, approval queue, incidents (spec §11–§13, §15).

## Scope

1. **Shifts** — `shifts` (user, date, scheduled start/end, property/service area nullable, manager, status, notes); states: `scheduled, confirmed, in_progress, completed, missed, cancelled, absent`.
2. **Attendance events** — `attendance_events` per spec §11.2 (user, shift, type `clock_in|break_start|break_end|clock_out|manual_correction|supervisor_override`, server + device timestamps, lat/lng, accuracy, effective radius, property, distance, inside_geofence, device_id, source, offline flag, synced_at, remarks). Immutable originals.
3. **Attendance rules** (spec §11.3) — late/early detection, missed shift, overtime, work + break duration; corrections create adjustment records, never rewrite originals; `attendance_correction_requests` with supervisor approve/reject.
4. **GPS validation** (spec §12) — geodesic distance util, effective radius fallback (property → category → org → system), accuracy threshold, out-of-radius → `gps_exceptions` (policy: accept / accept-with-exception / require override / reject), integrity signals (mock location, device time diff, low accuracy, repeated out-of-radius, offline delay) stored as reviewable flags.
5. **Missing coordinates** — no crash; "GPS verification unavailable" state; queued geocoding; manager-approved exception recorded.
6. **Evidence** — `task_evidence` (task, uploader, type `before|during|after|issue|safety|access_problem|other`, path, original name, mime, size, dimensions, captured/uploaded timestamps, lat/lng, device, source, checksum, processing status); compression + thumbnails via queued jobs; signed/controlled URLs.
7. **Completion enforcement** (spec §13.2) — required checklist answered, min before/after photos, required remarks, GPS check-out when required, incident ack when required.
8. **Approvals** (spec §13.3) — `task_approvals` (task, action approve|reject|request_correction|reopen, reviewer, timestamp, remarks, reason code, requested corrections, previous state); approval queue for supervisor; cleaner cannot approve own task.
9. **Incidents** (spec §15) — `incidents` + `incident_evidence` (task, property, reporter, category, severity, description, images, GPS, status `open|acknowledged|investigating|resolved|closed`, assigned reviewer, resolution).

## Permissions used

`5.1` view shifts, `5.2` manage shifts, `6.1` view attendance, `6.2` correct attendance, `8.1` view incidents, `8.2` manage incidents, `4.5` approve tasks.

## API

Attendance clock-in/break/clock-out, correction requests, task check-in/check-out/evidence/incidents, supervisor approval queue + approve/reject/request-correction/reopen, team-status.

## Exit criteria (spec §25 Phase 3)

Full E2E workflow passes; invalid state changes rejected; GPS exceptions reviewable; evidence requirements enforced.