# Role-Permission Matrix

Permissions defined in `config/permissions.php`. Dotted keys: granting parent implies children (`1` ⇒ `1.1`). `*` = all (admin).

Roles: `0` admin, `1` supervisor, `2` cleaner.

| Key | Permission | 0 admin | 1 supervisor | 2 cleaner |
|---|---|---|---|---|
| 1 | Settings | ✅ | ❌ | ❌ |
| 1.1 | Settings > Users | ✅ | ❌ | ❌ |
| 1.2 | Settings > Roles & Permissions | ✅ | ❌ | ❌ |
| 1.3 | Settings > Own Profile | ✅ | ❌ | ✅ |
| 1.4 | Settings > Organization | ✅ | ❌ | ❌ |
| 2 | Personnel | ✅ | ❌ | ❌ |
| 2.1 | Personnel > View | ✅ | ✅ | ❌ |
| 2.2 | Personnel > Create | ✅ | ✅ | ❌ |
| 2.3 | Personnel > Edit | ✅ | ✅ | ❌ |
| 2.4 | Personnel > Delete | ✅ | ❌ | ❌ |
| 3 | Properties | ✅ | ❌ | ❌ |
| 3.1 | Properties > View | ✅ | ✅ | ✅ |
| 3.2 | Properties > Create | ✅ | ✅ | ❌ |
| 3.3 | Properties > Edit | ✅ | ✅ | ❌ |
| 3.4 | Properties > Categories | ✅ | ❌ | ❌ |
| 3.5 | Properties > Tags | ✅ | ❌ | ❌ |
| 3.6 | Properties > Assignments | ✅ | ✅ | ❌ |
| 4 | Tasks | ✅ | ❌ | ❌ |
| 4.1 | Tasks > View | ✅ | ✅ | ✅ |
| 4.2 | Tasks > Create | ✅ | ✅ | ❌ |
| 4.3 | Tasks > Assign | ✅ | ✅ | ❌ |
| 4.4 | Tasks > Update Status | ✅ | ❌ | ✅ |
| 4.5 | Tasks > Approve | ✅ | ✅ | ❌ |
| 4.6 | Tasks > Cancel / Reopen | ✅ | ✅ | ❌ |
| 4.7 | Tasks > Task Types | ✅ | ❌ | ❌ |
| 4.8 | Tasks > Checklists | ✅ | ❌ | ❌ |
| 5 | Shifts | ✅ | ❌ | ❌ |
| 5.1 | Shifts > View | ✅ | ✅ | ❌ |
| 5.2 | Shifts > Manage | ✅ | ✅ | ❌ |
| 6 | Attendance | ✅ | ❌ | ❌ |
| 6.1 | Attendance > View | ✅ | ✅ | ✅ |
| 6.2 | Attendance > Correct | ✅ | ✅ | ❌ |
| 7 | Reports | ✅ | ❌ | ❌ |
| 7.1 | Reports > View | ✅ | ✅ | ❌ |
| 7.2 | Reports > Export | ✅ | ✅ | ❌ |
| 8 | Incidents | ✅ | ❌ | ❌ |
| 8.1 | Incidents > View | ✅ | ✅ | ❌ |
| 8.2 | Incidents > Manage | ✅ | ✅ | ❌ |
| 9 | Audit | ✅ | ❌ | ❌ |
| 9.1 | Audit > View | ✅ | ❌ | ❌ |

## Route protection

- `->middleware('permission:3.2')` — single key
- `->middleware('permission:4.1,7.1')` — any-of (OR)
- `->middleware(['permission:4.5', 'role:1'])` — both must pass (AND)
- `->middleware('role:0,1')` — role any-of
- No middleware = public

Row-level scope (supervisor sees only assigned) enforced in services/queries, not middleware (see `work/personnel/design.md`).