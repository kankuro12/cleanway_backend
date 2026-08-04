# Personnel & Teams — Suggestions

- **Status transitions**: validate allowed moves (invited→active, active→suspended, suspended→active, active→archived…); archive instead of delete (spec §0.9).
- **One user table**: manager/supervisor/cleaner are `role` values, not separate tables — keeps queries and permissions simple.
- **Scope service**: central `PersonnelScope` used by web + API + reports; avoids duplicated `where` clauses everywhere (spec §0.5).
- **manager_assignments over manager_id FK**: FK is convenience for fast queries; authoritative history lives in `manager_assignments` (dated). Keep both, write both in the personnel action.
- **Cache**: roles/permissions already config-cached; cache branch/team name lookups where hot.
- **Indexes**: `status`, `branch_id`, `team_id`, `manager_id` on users.
- **Deactivate, don't delete**: `archived` status + soft delete; audit logs never reference dead rows.