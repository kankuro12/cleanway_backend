# Personnel & Teams

Objective: manage personnel (admin/supervisor/cleaner), teams, branches, and reporting lines with role-scoped visibility (spec §5, §6).

## Scope

1. **Users extension** — extend `users` with nullable optional fields (spec §6):
   - `employee_no`, `phone`, `profile_image`, `emergency_contact`, `branch_id`, `team_id`, `manager_id`, `employment_type`, `start_date`, `end_date`, `skills`, `certifications`, `default_working_hours`, `service_areas`, `notification_preferences`.
   - `status` enum: `invited, active, inactive, suspended, on_leave, archived` (default `active`).
2. **Branches** — `branches` table (name, address, active).
3. **Teams** — `teams` table + `team_members` pivot (user_id, team_id, role_in_team, joined_at).
4. **Manager assignments** — `manager_assignments` (manager_id, user_id, branch/team/service area, start/end date) with temporary assignment effective dates (spec §6).
5. **Personnel activity history** — via audit framework (create/update/status change).
6. **Invite flow** — optional: invite → password set; status `invited` blocks login.
7. **Scoped visibility** — supervisor sees only branch/team/assigned cleaners (scope service shared by web + API).

## Permissions used

`2.1` view, `2.2` create, `2.3` edit, `2.4` delete (admin only), `2.5` assign-managers (proposed).

## Web UI

Personnel list (filter: role/status/branch/team, search), create/edit form, detail page (assigned cleaners for supervisor), teams/branches admin pages.

## API

`GET /api/v1/me`, `GET /api/v1/supervisor/team-status` (supervisor scope), personnel CRUD behind `2.x` keys.

## Exit criteria

Roles enforced; supervisor scope respected; teams/branches/manager assignments creatable; personnel changes audited.