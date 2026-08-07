# Ionic Mobile App — Master Plan (Root)

Objective: native iOS + Android app for cleaners, supervisors, admins — consuming the existing Laravel API (`routes/api.php`, Sanctum bearer, permission-key gated). Mirrors web field-ops workflows; offline-tolerant for in-field use. No new backend logic except the small API additions listed below — app is a thin client over the existing contract.

Operating contract: `../AGENTS.md` (runtime notes + conventions) · Backend contract: `../routes/api.php` + `../app/Http/Controllers/Api/V1/`.

## Delivery Order

1. **Foundation** — scaffold, theming, auth, shell, API client, permissions runtime.
2. **Tasks** — cleaner core: my tasks, detail, transitions, GPS, evidence, incidents, all-tasks list.
3. **Attendance** — clock in/out, breaks, shifts, corrections.
4. **Notifications** — feed, FCM push, deep links.
5. **Personnel & Properties** — lists, filters, team status, property detail w/ map.
6. **Admin Console** — dashboard widgets, attention queue, approvals.
7. **Reports** — read-only reports + audit viewer.
8. **Offline & Hardening** — sync engine, runtime permissions, assets, release.

Ordering rationale: cleaner value first (largest user group); supervisor/admin phases wait on the backend additions.

## Modules

| Module | Folder | Priority | Status |
|---|---|---|---|
| Foundation | [foundation](foundation/plan.md) | 1 | planned |
| Tasks | [tasks](tasks/plan.md) | 2 | planned |
| Attendance | [attendance](attendance/plan.md) | 3 | planned |
| Notifications | [notifications](notifications/plan.md) | 4 | planned |
| Personnel | [personnel](personnel/plan.md) | 5 | planned |
| Properties | [properties](properties/plan.md) | 5 | planned |
| Admin console | [admin-console](admin-console/plan.md) | 6 | planned (needs backend adds) |
| Reports | [reports](reports/plan.md) | 7 | planned (needs backend adds) |
| Offline & Hardening | [offline-hardening](offline-hardening/plan.md) | 8 | planned |

Each module folder tracks `plan.md` (screens, APIs, forms, flows), `current.md` (done/doing), `future.md` (deferred), `suggestion.md` (recommendations), `design.md` (component architecture + UX).

## App Structure (screens & navigation)

- **Auth flow**: Login → (after token) role-aware tab shell. Logout + forced re-login on 401/expired token.
- **Tabs by role** (derived from `/me` → `role` + permissions):

| Tab | cleaner | supervisor | admin |
|---|---|---|---|
| Dashboard | — | ✓ | ✓ |
| Approvals | — | ✓ | ✓ |
| My Tasks / Tasks | ✓ (own, 4.1) | ✓ (all, 4.9) | ✓ (all, 4.9) |
| Clock | ✓ (6.1) | — | — |
| Team / Personnel | — | ✓ (2.1) | ✓ (2.1) |
| Notifications | ✓ | ✓ | ✓ |
| Profile | ✓ | ✓ | ✓ |

- **Stack screens** (pushed, deep-linkable): Task detail · Task create (4.2, minimal) · Incident report · Evidence viewer · Property detail · Shift detail · Correction form · Approvals decision · Report pages · Audit viewer.
- **Shared UI**: status badges (dot + mono label, never color-only), task/attendee cards, filter bottom sheet + active-filter pills, pull-to-refresh, infinite scroll, empty/error/loading states, 44–48px touch targets, bottom action strips.

## Master API Inventory

All `Authorization: Bearer <token>` except login. Envelope: `data` (+ `meta.pagination`: total/per_page/current_page/last_page). Errors: `{message}` (401/403/422/429). Throttle: login 10/min, API 120/min.

### Auth & profile
| Method | Path | Permission | Key request fields | Response highlights |
|---|---|---|---|---|
| POST | /auth/login | public | email, password, device_name (optional) | user resource + plainTextToken |
| POST | /auth/logout | any | — | null |
| GET | /me | any | — | user: id, name, email, role (0 admin/1 supervisor/2 cleaner), status, employee_no, phone, employment_type, branch_id, team_id, manager_id, start_date, end_date, skills |

### Personnel (2.x)
| Method | Path | Permission | Key request fields | Notes |
|---|---|---|---|---|
| GET | /personnel | 2.1 | filters: search, role, status, branch_id | paginated |
| GET | /personnel/{id} | 2.1 | — | detail |
| POST | /personnel | 2.2 | user create fields (mirror web) | admin only |
| PUT | /personnel/{id} | 2.3 | update fields | admin only |
| DELETE | /personnel/{id} | 2.4 | — | admin only |
| GET | /supervisor/team-status | 2.1 | — | team roster + statuses |

### Properties (3.x)
| Method | Path | Permission | Key request fields | Notes |
|---|---|---|---|---|
| GET | /properties | 3.1 | filters: search, active, category_id, tag_id, geocode_status, missing_coords, unassigned, assigned_to | paginated |
| GET | /properties/search | 3.1 | q | quick search |
| GET | /properties/{id} | 3.1 | — | detail |
| GET | /property-categories | 3.1 | — | reference list |
| GET | /property-tags | 3.1 | — | reference list |
| POST | /properties | 3.2 | property fields (mirror web) | — |
| PUT | /properties/{id} | 3.3 | update fields | — |
| DELETE | /properties/{id} | 3.3 | — | — |
| POST | /properties/{id}/retry-geocode | 3.3 | — | re-run geocoding |

### Tasks (4.x)
| Method | Path | Permission | Key request fields | Notes |
|---|---|---|---|---|
| GET | /me/tasks | any | filters: status, priority, from, to; per_page | own tasks; order by scheduled_start_at |
| GET | /tasks | 4.9 | filters: status, priority, task_type_id, property_id, assignee_id, from, to; per_page | all tasks |
| GET | /tasks/{id} | 4.1 | — | detail w/ taskType, property, assignments, checklistSnapshot, subtasks |
| POST | /tasks/{id}/transition | 4.4 | status ∈ accepted\|declined\|start\|pause\|resume\|complete\|submit; remarks, latitude, longitude | auto-submits when approval_required (backend policy); 422 w/ message on illegal transition |
| POST | /tasks/{id}/check-in | 4.4 | GPS fields (see below) | 403 blocked w/ exception; returns event_id, inside_geofence, blocked, task_status |
| POST | /tasks/{id}/check-out | 4.4 | GPS fields | same shape |
| POST | /tasks/{id}/evidence | 4.4 | multipart image ≤10 MB, evidence_type, captured_at, latitude, longitude, device_id | 201; returns id, type, size, processing_status |
| POST | /tasks/{id}/complete | 4.4 | responses[] (snapshot_item_id, value), remarks, latitude, longitude | 422 w/ missing checklist items |
| POST | /tasks/{id}/incidents | 4.4 | category, severity, description, latitude, longitude | 201; returns id, uuid, status |

**Shared GPS fields** (check-in/out, clock in/out, breaks): latitude, longitude, gps_accuracy_meters (0–10000), device_timestamp, device_id, offline, is_mock_location, remarks (clock only), shift_id (clock-in only).

### Attendance (6.x)
| Method | Path | Permission | Key request fields | Notes |
|---|---|---|---|---|
| POST | /attendance/clock-in | any | GPS fields + shift_id | 202 when outside geofence; response has distance_from_property_meters, inside_geofence, exception |
| POST | /attendance/break/start | any | GPS fields | — |
| POST | /attendance/break/end | any | GPS fields | — |
| POST | /attendance/clock-out | any | GPS fields | — |
| GET | /me/shifts | any | per_page | own shifts: date, scheduled start/end, property, status, summary (worked/break/overtime) |
| POST | /attendance/corrections | any | original_event_id, reason | own events only (403 otherwise) |

### Notifications & devices
| Method | Path | Permission | Key request fields | Notes |
|---|---|---|---|---|
| GET | /notifications | any | read (0/1 tabs), per_page | paginated |
| POST | /notifications/{id}/read | any | — | marks read |
| POST | /me/devices | any | fcm_token, platform (web\|android\|ios), device_id | upsert by fcm_token |
| DELETE | /me/devices/{token} | any | — | unregister |

## Master Form Inventory

| Form | Screen | Fields | Validations |
|---|---|---|---|
| Login | login | email, password | email format; credentials check (422) |
| Task create (4.2, minimal) | task-create | title, description, task_type_id, property_id, priority, scheduled_start_at, assignee_id | required; mirror web task-create fields |
| Task transition | task-detail action strip | status (from button), remarks (optional), latitude, longitude (auto) | status in allowed set |
| Check-in / check-out | task-detail | GPS fields (auto-filled from geolocation; accuracy, timestamp, device_id) | lat −90..90, lng −180..180, accuracy ≤10000 |
| Evidence upload | task-detail | photo file, evidence_type, captured_at, latitude, longitude, device_id | image ≤10 MB |
| Complete task | task-detail | checklist responses (per snapshot_item_id: value), remarks, lat/lng | all required items present (422 lists missing) |
| Incident report | incident-report | category, severity, description, latitude, longitude | enums; description ≤5000 |
| Clock in | clock screen | GPS fields + shift_id | shift must exist |
| Break start/end, clock out | clock screen | GPS fields | — |
| Correction request | correction | original_event_id, reason | own event; reason ≤1000 |
| Notification mark-read | notification list | (tap) | — |
| Device register | post-login (silent) | fcm_token, platform, device_id | token ≤512 |
| Approvals decision (new API) | approvals | decision (approve/reject), reason | 4.5 |
| Filters (shared pattern) | every list screen | per-list query params (see module plans) | passed through to API query string |

## Backend work (additions the app needs — permission-gated, cached, tested)

1. `GET /attendance/events` — log w/ filters (user_id, event_type, from, to) — 6.1.
2. `GET /shifts` — shift board w/ filters (date, user_id, status) + `PATCH /shifts/{id}/status` — 5.2.
3. `GET /incidents` + `GET /incidents/{id}` — list/detail — 8.2.
4. `GET /dashboard/widgets` — stat cards + attention queue (reuse `DashboardWidgets` domain logic).
5. `GET /approvals` + `POST /approvals/{id}/decision` — queue + decision — 4.5.
6. `GET /reports/{key}` + `GET /audit` — read-only mirrors — 7.x / 9.x.
7. **Incident photos**: web incident form has a photos input but the incident API accepts none — confirm web flow; add photo support (or evidence-link) if web has it, else drop from mobile scope.

All additions: controller action (no closures), permission middleware, cache where applicable, transactions on writes, PHPUnit feature tests, audit entries for writes. See `../work/plan.md` conventions.

## Cross-Cutting Rules

- App gates UI by permission keys (same numbers as web); real authorization stays server-side.
- Design tokens ported 1:1 from `../public/css/tokens.css` (+ components.css semantics) — navy shell, safety-orange accent (dark text on orange), Archivo 900 + IBM Plex Mono, mono uppercase micro-labels, status = dot + label, never color alone.
- Offline: outbox queue for transitions/evidence/incidents/clock (GPS + timestamp captured at action time); sync on reconnect; conflicts surfaced as 422 messages.
- Accessibility: real labels, one title per screen, focus states, contrast ≥4.5:1, reduced-motion respected, 48px targets.
- Tests: Vitest for API client + offline queue; backend PHPUnit for new endpoints; manual smoke matrix per phase (Android 12+, iOS 16+, airplane-mode offline).

## Definition of Done (per module)

Screens + flows from module plan work on both platforms; forms match inventory; offline path for write actions; permission gating honored; backend additions green (`php84 artisan test`); error/loading/empty states; theme parity; `current.md` updated.
