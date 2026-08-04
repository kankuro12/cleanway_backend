# Personnel & Teams — Current

## Done

- [x] Migrations: `branches`, `teams`, `team_members`, `manager_assignments`; `users` extended (employee_no, phone, profile_image, emergency_contact, branch/team/manager FKs, employment_type, start/end dates, skills, certifications, working hours, service areas, notification prefs, status enum, soft deletes).
- [x] Models: `Branch`, `Team`, `TeamMember`, `ManagerAssignment`; `User` relations (branch/team/manager/managedUsers/teams/managerAssignments) + status constants + `scopeFilter`.
- [x] `PersonnelScope` — admin all, supervisor (branch/team/managed/self), cleaner self.
- [x] Web: `PersonnelController` (index/create/store/edit/update/archive), list w/ filters + pagination, create/edit forms.
- [x] Web: `BranchController` (index/store/update active), `TeamController` (index/store/update, member add/remove).
- [x] Sidebar: Personnel, Branches, Teams links.
- [x] API (`/api/v1`): Sanctum login/logout/me (token issuance + revocation), personnel CRUD behind `2.x` permissions, `/supervisor/team-status`, `UserResource`, pagination envelope.
- [x] Routes: web (permission-gated `2.x`) + api registered in `bootstrap/app.php` (`apiPrefix: api/v1`).
- [x] Seeder: `PersonnelSeeder` (branch, supervisor, cleaner, team + membership).
- [x] Fixes: `#[SoftDeletes]` attribute does not exist in Laravel 13 — replaced with `SoftDeletes` trait (was hard-deleting); `PersonnelScope` null-column leak (`team_id IS NULL` matched everyone).

## Verified

- 40 tests green (incl. 11 new personnel/API tests), pint clean.
- Scope: supervisor sees branch/team/managed only; cleaner blocked from personnel list (403, no `2.1` grant) — sees self via `/api/v1/me`.
- Archive = soft delete + `archived` status; self-delete blocked; API logout revokes token.

## Next

1. `manager_assignments` UI (dated assignments) — table exists, management screen deferred.
2. Invite flow (status `invited` blocks login) — optional, deferred.
3. Personnel activity history view (uses audit framework).
4. Move to properties module.