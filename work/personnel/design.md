# Personnel & Teams — Design

## ER

```
branches ─< users >──────── team_members >─── teams
              │  │
              │  └─ manager_id (self-ref users)
              └─ branch_id ──> branches
manager_assignments: id, manager_id, user_id, scope_type(branch|team|service_area), scope_id, start_date, end_date, active
```

- `users.manager_id` self FK = fast default manager; authoritative history = `manager_assignments`.
- `team_members`: user_id, team_id, role_in_team, joined_at, left_at.
- Soft deletes on users/branches/teams; `archived` status on users.

## Schema (users additions)

`employee_no` unique nullable, `phone`, `profile_image_path`, `emergency_contact` json, `branch_id` FK, `team_id` FK, `manager_id` FK, `employment_type` enum, `start_date`, `end_date`, `status` enum default active, `skills` json, `certifications` json, `default_working_hours` json, `service_areas` json, `notification_preferences` json.

## Key services

- `app/Domain/Personnel/ManagePersonnel.php` — create/update with audit + assignment sync (transactional).
- `app/Support/PersonnelScope.php` — query scopes per role.
- Policies/actions gate on `permission:2.x` + scope.

## Tests

- Role CRUD matrix (admin all, supervisor scoped, cleaner none).
- Status transition validity.
- Manager assignment history + temporary dates.
- Team membership add/remove.
- Audit entries on every change.