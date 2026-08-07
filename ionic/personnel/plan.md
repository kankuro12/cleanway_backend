# Personnel

Objective: supervisor/admin personnel directory w/ filters + team status; own profile (already in foundation); admin CRUD minimal (web remains the primary admin surface).

## Screens & structures

1. **Personnel list** (2.1) — cards: avatar initials, name, role badge, status (active/inactive), employee_no, branch/team; filter sheet (search, role, status, branch_id) + pills; pull-to-refresh + infinite scroll.
2. **Personnel detail** (2.1) — all UserResource fields (phone, employment_type, branch, team, manager, start/end dates, skills), call/SMS action, tasks link (deep link to all-tasks filtered by assignee — 4.9).
3. **Team status** (2.1) — `/supervisor/team-status`: roster w/ today's status (on shift / clocked in / task in progress / off) — mirrors web team-status panel.
4. **Admin CRUD (2.2–2.4)** — minimal create/edit form + archive; low priority — web remains primary.

## APIs used

`GET /personnel` (filters search, role, status, branch_id), `GET /personnel/{id}`, `GET /supervisor/team-status`, `POST/PUT/DELETE /personnel` (admin only).

## Forms

| Form | Fields | Notes |
|---|---|---|
| Filter sheet | search, role (enum), status, branch_id | → query params |
| Personnel create (2.2) | name, email, password, role, status, employee_no, phone, employment_type, branch_id, team_id, manager_id, start_date, skills | mirror web form |
| Personnel update (2.3) | same minus password (reset flow separate) | — |
| Archive (2.4) | — | confirm dialog |

## Exit criteria

Supervisor browses + filters personnel and reads team status on phone; admin CRUD smoke-tested once (web remains canonical).
