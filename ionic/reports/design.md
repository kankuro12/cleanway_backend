# Reports — Design

## Components

- `ReportScreen` — generic: period pickers + stat rows + table; one component parameterized by report key (no per-report components — data shapes stay simple rows/stat pairs).
- `AuditRow` — mono timestamps, entity/action chips, user, changes preview (tap → detail expand).

## State & data

- Report data: `GET /reports/{key}` per key + period; cache per (key, period); pull-to-refresh.
- Audit: paginated, filter params → query string; same list pattern as other modules.

## Backend contract (adds)

- Report payload: `summary` (label/value pairs) + `rows` (flat objects, keys as column labels) — generic enough for any web report to map 1:1; columns derived from first row keys.
- Audit payload: standard paginated list w/ `changes` as JSON string (web format parity).
- `format=csv` returns file download — Capacitor share sheet passes the file to system share; if too fiddly, defer export (suggestion: defer — share sheets are platform rabbit holes).

## Testing

- Vitest: generic column derivation from rows, period param serialization.
- Manual: two reports match web numbers; audit filter round-trip.
