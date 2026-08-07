# Cleanway API — Endpoint Index

Complete API reference for building clients **without backend code access**. Every endpoint has a dedicated doc under [`api/`](api/) with full request fields, validations, and response shapes. All details derived from `routes/api.php`, controllers, form requests, and resources.

## Conventions

- **Base URL**: `<app-url>/api/v1`
- **Authentication**: `Authorization: Bearer <token>` (Sanctum) on every route except `POST /auth/login`. Token returned by login as `data.token` (plain text — store securely).
- **Throttles**: `POST /auth/login` → 10 req/min/IP. All other authenticated routes → 120 req/min.
- **Success envelope**: `{"data": <payload>}`. Lists additionally return `meta.pagination`:
  `{"total": int, "per_page": int, "current_page": int, "last_page": int}` (`personnel-index` also includes `links`).
- **Error envelopes**:
  | Status | Body |
  |---|---|
  | 401 | `{"message":"Unauthenticated."}` (missing/expired token) |
  | 403 | `{"message":"You do not have permission to perform this action."}` (permission key or ownership) — some endpoints return custom messages instead |
  | 404 | `{"message":"Not Found"}` (missing model/route) |
  | 422 | `{"message":"<first validation error>","errors":{ "<field>": ["<message>", ...] }}` |
  | 429 | `{"message":"Too Many Requests"}` |
  | 202 | clock-in outside geofence (still records — see attendance docs) |
- **Datetimes**: ISO 8601 UTC strings. **Dates**: `YYYY-MM-DD`. Booleans are JSON booleans; numeric strings accepted where numeric fields documented.
- **Multipart**: evidence upload uses `multipart/form-data`; file field name `evidence`.
- **Permission keys** (`permission:<key>` middleware) are the same numbers used in the web admin. Absence → 403.

## Enum reference (used across endpoints)

| Field | Values |
|---|---|
| user role | `0` admin, `1` supervisor, `2` cleaner |
| user status | `invited`, `active`, `inactive`, `suspended`, `on_leave`, `archived` |
| task status | `draft`, `scheduled`, `unassigned`, `assigned`, `accepted`, `declined`, `in_progress`, `paused`, `delayed`, `unable_to_access`, `completed`, `submitted_for_approval`, `correction_requested`, `rejected`, `reopened`, `approved`, `cancelled` |
| transition status (request) | `accepted`, `declined`, `start`, `pause`, `resume`, `complete`, `submit` |
| property geocode_status | `pending`, `resolved`, `manually_adjusted`, `failed` |
| incident category | `property_access_problem`, `missing_key`, `incorrect_access_code`, `damaged_equipment`, `property_damage`, `safety_hazard`, `missing_supplies`, `unsafe_situation`, `task_cannot_be_completed`, `other` |
| incident severity | `low`, `medium`, `high`, `critical` |
| incident status | `open`, `acknowledged`, `investigating`, `resolved`, `closed` |
| evidence evidence_type | `before`, `during`, `after`, `issue`, `safety`, `access_problem`, `other` |
| attendance event_type | `clock_in`, `break_start`, `break_end`, `clock_out`, `manual_correction`, `supervisor_override` |
| shift status | `scheduled`, `confirmed`, `in_progress`, `completed`, `missed` |
| device platform | `web`, `android`, `ios` |
| checklist item type | `yes_no`, `pass_fail`, `text`, `numeric`, `photo` |
| correction decision | `pending`, `approved`, `rejected` |

**Shared GPS fields** (task check-in/out, clock in/out, breaks): `latitude` (numeric −90..90), `longitude` (numeric −180..180), `gps_accuracy_meters` (int 0..10000), `device_timestamp` (ISO 8601), `device_id` (string ≤100), `offline` (bool), `is_mock_location` (bool).

---

## Auth

| Method | Path | Permission | Doc |
|---|---|---|---|
| POST | `/auth/login` | public (10/min) | [auth-login](api/auth-login.md) |
| POST | `/auth/logout` | any | [auth-logout](api/auth-logout.md) |
| GET | `/me` | any | [me](api/me.md) |

## Personnel (permission 2.x)

| Method | Path | Permission | Doc |
|---|---|---|---|
| GET | `/personnel` | 2.1 | [personnel-index](api/personnel-index.md) |
| GET | `/personnel/{user}` | 2.1 | [personnel-show](api/personnel-show.md) |
| POST | `/personnel` | 2.2 | [personnel-store](api/personnel-store.md) |
| PUT | `/personnel/{user}` | 2.3 | [personnel-update](api/personnel-update.md) |
| DELETE | `/personnel/{user}` | 2.4 | [personnel-destroy](api/personnel-destroy.md) |
| GET | `/supervisor/team-status` | 2.1 | [personnel-team-status](api/personnel-team-status.md) |

## Properties (permission 3.x)

| Method | Path | Permission | Doc |
|---|---|---|---|
| GET | `/properties` | 3.1 | [properties-index](api/properties-index.md) |
| GET | `/properties/search` | 3.1 | [properties-search](api/properties-search.md) |
| GET | `/properties/{property}` | 3.1 | [properties-show](api/properties-show.md) |
| GET | `/property-categories` | 3.1 | [properties-categories](api/properties-categories.md) |
| GET | `/property-tags` | 3.1 | [properties-tags](api/properties-tags.md) |
| POST | `/properties` | 3.2 | [properties-store](api/properties-store.md) |
| PUT | `/properties/{property}` | 3.3 | [properties-update](api/properties-update.md) |
| DELETE | `/properties/{property}` | 3.3 | [properties-destroy](api/properties-destroy.md) |
| POST | `/properties/{property}/retry-geocode` | 3.3 | [properties-retry-geocode](api/properties-retry-geocode.md) |

## Tasks (permission 4.x)

| Method | Path | Permission | Doc |
|---|---|---|---|
| GET | `/me/tasks` | any | [tasks-me](api/tasks-me.md) |
| GET | `/tasks` | 4.9 | [tasks-index](api/tasks-index.md) |
| GET | `/tasks/{task}` | 4.1 | [tasks-show](api/tasks-show.md) |
| POST | `/tasks/{task}/transition` | 4.4 | [tasks-transition](api/tasks-transition.md) |
| POST | `/tasks/{task}/check-in` | 4.4 | [tasks-check-in](api/tasks-check-in.md) |
| POST | `/tasks/{task}/check-out` | 4.4 | [tasks-check-out](api/tasks-check-out.md) |
| POST | `/tasks/{task}/evidence` | 4.4 | [tasks-evidence](api/tasks-evidence.md) |
| POST | `/tasks/{task}/complete` | 4.4 | [tasks-complete](api/tasks-complete.md) |
| POST | `/tasks/{task}/incidents` | 4.4 | [tasks-incidents](api/tasks-incidents.md) |

## Attendance (own clocking — any authenticated user)

| Method | Path | Permission | Doc |
|---|---|---|---|
| POST | `/attendance/clock-in` | any | [attendance-clock-in](api/attendance-clock-in.md) |
| POST | `/attendance/break/start` | any | [attendance-break-start](api/attendance-break-start.md) |
| POST | `/attendance/break/end` | any | [attendance-break-end](api/attendance-break-end.md) |
| POST | `/attendance/clock-out` | any | [attendance-clock-out](api/attendance-clock-out.md) |
| GET | `/me/shifts` | any | [attendance-me-shifts](api/attendance-me-shifts.md) |
| POST | `/attendance/corrections` | any | [attendance-corrections](api/attendance-corrections.md) |

## Notifications (any authenticated user)

| Method | Path | Permission | Doc |
|---|---|---|---|
| GET | `/notifications` | any | [notifications-index](api/notifications-index.md) |
| POST | `/notifications/{notification}/read` | any | [notifications-read](api/notifications-read.md) |

## Devices (FCM registration — any authenticated user)

| Method | Path | Permission | Doc |
|---|---|---|---|
| POST | `/me/devices` | any | [devices-store](api/devices-store.md) |
| DELETE | `/me/devices/{token}` | any | [devices-destroy](api/devices-destroy.md) |
