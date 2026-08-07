# Admin Console — Design

## Components

- `StatCard` — Archivo 900 numeral, mono micro-label, tap → emits `data-filter` key (same keys as web `DashboardWidgets`); active state accent tint.
- `AttentionItem` — severity pill (mono, tinted) + Review action (48px).
- `ApprovalCard` — submitted info, remarks, reason; action strip: Approve (accent) / Reject (danger), reason prompt on reject.

## State & data

- Dashboard: `GET /dashboard/widgets` on tab enter + pull-to-refresh; numbers cached until next refresh (server-side cache per project conventions too).
- Approvals: queue list paginated; decision POST → remove item from queue + toast; on error (422/403) show `{message}`.
- Drill-down: stat filter key → All Tasks list pre-filtered (reuse tasks module route w/ params).

## Backend contract (adds)

- Widgets payload should match web widget semantics (filter key per card) so the app renders identically — no new shape invented; response: cards[] {label, value, filter} + attention[] {type, severity, title, link}.
- Approvals decision reuses existing approval policy/domain action; audit entry on decision.

## Testing

- Vitest: widget→filter mapping, approval optimistic removal rollback, severity pill mapping.
- Manual: approve/reject round-trip, stat drill-down parity with web.
