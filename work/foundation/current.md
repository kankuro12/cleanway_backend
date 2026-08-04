# Foundation — Current

## Done

- [x] RBAC core: `config/permissions.php` (roles 0/1/2, dotted keys `1`–`9`, wildcard `*`).
- [x] `permission` + `role` middleware (`EnsurePermission`, `EnsureRole`) registered in `bootstrap/app.php`.
- [x] `User` model: role column (tinyint, default 2), `hasPermission/hasAnyPermission/hasRole/hasAnyRole`.
- [x] Migration `2026_08_04_000000_add_role_to_users_table`.
- [x] Demo routes in `routes/web.php` (public + protected + group + combined).
- [x] Tests: `tests/Feature/PermissionMiddlewareTest.php` — 10 green.
- [x] `.env` + app key generated.

## In Progress

- Auth scaffold (login route missing — `auth` middleware currently errors without `route('login')`).
- Sanctum install for API tokens.

## Next

1. `LoginController` + `login` route + admin seeder.
2. `composer require laravel/sanctum`.
3. `audit_logs` table + `AuditLogger` + `Auditable` trait.
4. Queue/scheduler wiring; S3 disk config.
5. `docs/permission-matrix.md`, `docs/design-tokens/tokens.json`.
6. Pint + CI workflow.