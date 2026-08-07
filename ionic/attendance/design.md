# Attendance — Design

## Components

- `ClockCard` — state machine driven: derived from last event (`event_type` + time), not from local guesses; disable buttons during in-flight request; show `inside_geofence`/distance chip.
- `ShiftCard` — property name, mono date/time micro-labels, status badge, summary line (worked/break/overtime).
- `EventTrail` — timeline of events w/ server vs device timestamp, geofence chip, exception line.

## State & data

- Clock state source: `/me/shifts` (current shift incl. `summary`) refreshed on tab enter + after every action. Do not trust stored UI state across launches.
- Actions are one-shot POSTs; on 201/202 refetch shifts; on 403/422 show `{message}` banner.
- Shift list cached per page; pull-to-refresh invalidates.

## GPS handling

- Reuse shared Geolocation service; clock-in sends `shift_id` when a shift is active today (from me/shifts) — otherwise null.
- `offline` flag true when network down (Phase 8 outbox takes over; event still timestamped/GPS'd locally).

## Supervisor board (after backend add)

- Same `FilterSheet` pattern; status update via PATCH; optimistic update + rollback on error.

## Testing

- Vitest: clock state derivation (last-event table), geofence banner logic, correction event picker filter (own events only).
- Manual: clock round-trip both platforms + airplane-mode clock-out (outbox).
