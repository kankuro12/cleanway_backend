# Attendance

Objective: reliable field clocking (clock in/out, breaks) with GPS integrity, own shift view, correction requests; supervisor shift board (needs backend addition).

## Screens & structures

1. **Clock tab** (cleaner, 6.1) — current-shift status card (state derived from own latest event): not-started / clocked-in / on-break / clocked-out; big primary action button (48px) switches by state; live clock; GPS accuracy + geofence indicator; offline indicator (Phase 8).
2. **My Shifts list** — own shifts (date desc): date, property, scheduled start/end, status badge, summary (worked time, break time, overtime); pull-to-refresh + infinite scroll; tap → shift detail.
3. **Shift detail** — scheduled times, property, status, summary values, event trail (event type + server timestamp + inside_geofence + exception) — event trail uses `me/shifts` + clock response payloads; full event log API is a backend addition (used by supervisor view).
4. **Corrections** — from clock/shift overflow: pick an event to correct (own events only), reason; list of own correction requests w/ decision status.
5. **Supervisor shift board** (5.2, backend add) — shifts w/ filters (date, user_id, status), set-status action.

## APIs used

`POST /attendance/clock-in` (GPS fields + shift_id; 202 when outside geofence), `POST /attendance/break/start`, `POST /attendance/break/end`, `POST /attendance/clock-out`, `GET /me/shifts`, `POST /attendance/corrections` (original_event_id, reason). Backend adds: `GET /attendance/events`, `GET /shifts`, `PATCH /shifts/{id}/status`.

## Forms

| Form | Fields | Notes |
|---|---|---|
| Clock in | GPS fields + shift_id | 202 → outside geofence banner, still record |
| Break start/end | GPS fields | — |
| Clock out | GPS fields | — |
| Correction | original_event_id (picker of own events), reason (≤1000) | 403 if not own event |
| Shift set-status (5.2) | status (late/early/missed/…), reason | backend add |

## Flows

- **Clocking**: open Clock tab → geolocation fix → tap action → response (201/202) → status card updates; outside-geofence shows distance + exception, event still recorded (backend decides).
- **Correction**: own event → reason → request; status tracked in list.

## Exit criteria

Full clock round-trip (in → break → resume → out) on both platforms with GPS attached; geofence warning shown; correction round-trips; supervisor shift board works (backend add done).
