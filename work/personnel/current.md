# Personnel & Teams — Current

## Done

- [x] `users` base table (name, email, password, role tinyint 0/1/2, timestamps).
- [x] UserFactory + role constants on model.

## In Progress

- Nothing started.

## Next

1. Migration: extend `users` (optional fields + status + branch/team/manager FKs).
2. `branches`, `teams`, `team_members`, `manager_assignments` migrations.
3. Models + relationships (`User::branch/team/manager/teamMembers/assignedCleaners`).
4. PersonnelController web + API, validation requests.
5. Scope service (supervisor/cleaner row-level filtering).
6. Seeder: demo admin, supervisor, cleaner, team, branch.
7. Tests: CRUD, scoped visibility, status transitions, audit trail.