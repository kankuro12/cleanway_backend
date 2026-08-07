# Reports

Objective: read-only mirror of key web reports + audit viewer for supervisors/admins. Lowest priority module; backend additions required. Web remains the canonical reporting surface — app covers "check numbers on the go".

## Screens & structures

1. **Reports index** — list of report keys (attendance summary, task completion, property activity, etc. — the set web exposes, backend add decides exact keys); tap → report screen.
2. **Report screen** — period selector (from/to — API filters), summary stat rows (Archivo numerals), row/table list; export/share (share sheet w/ PDF/CSV if backend add supports format param).
3. **Audit viewer** (9.x) — paginated audit trail (entity, action, user, timestamp, changes) w/ filter by entity/action; read-only.

## APIs used

Backend adds: `GET /reports/{key}` (filters from/to + per_page; optional `format=csv`), `GET /audit` (filters entity, action, user_id, from, to).

## Forms

| Form | Fields | Notes |
|---|---|---|
| Report period | from, to (date pickers) | → query params |
| Audit filter | entity, action, user_id, from, to | → query params |

## Exit criteria

Two reports render w/ period filter matching web numbers; audit trail browsable; share/export smoke-tested (or explicitly deferred if backend add excludes format support).
