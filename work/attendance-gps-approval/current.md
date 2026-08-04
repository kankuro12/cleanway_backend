# Attendance, GPS & Approval — Current

## Done

- Nothing (module not started).

## In Progress

- Nothing.

## Next

1. Migrations: `shifts`, `attendance_events`, `attendance_correction_requests`, `gps_exceptions`, `task_evidence`, `task_approvals`, `incidents`, `incident_evidence`.
2. `Geodesic` distance service + effective radius resolver (reuse from properties phase).
3. `RecordAttendanceEvent` + `CheckInToTask`/`CheckOutFromTask` actions (transactional, immutable events).
4. Attendance rules service (duration, overtime, late/early, missed).
5. Evidence upload + processing job (compress/thumbnail/checksum) + signed URLs.
6. `CompleteTask` enforcement + `SubmitTaskForApproval` + `ApproveTask`/`RejectTask`/`RequestTaskCorrection`/`ReopenTask`.
7. `RaiseIncident`/`ResolveIncident`.
8. Approval queue UI + supervisor scoped views.
9. Seeds: shifts, sample evidence config.
10. Tests: E2E scenario (spec §24.4), radius fallback, out-of-radius exception, corrections immutability, photo requirements, approval rules.