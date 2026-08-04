# Foundation — Current

## Done

- [x] RBAC core: `config/permissions.php` (roles 0/1/2, dotted keys `1`–`9`, wildcard `*`).
- [x] `permission` + `role` middleware (`EnsurePermission`, `EnsureRole`) registered in `bootstrap/app.php`.
- [x] `User` model: role column (tinyint, default 2), `hasPermission/hasAnyPermission/hasRole/hasAnyRole`.
- [x] Migration `2026_08_04_000000_add_role_to_users_table`.
- [x] `.env` + app key generated; sqlite db created + migrated.
- [x] Auth: login/logout (`LoginController`), login page, `guest` redirect for root.
- [x] Forgot/reset password: `ForgotPasswordController`, `ResetPasswordController`, views, routes, tests (PasswordResetTest).
- [x] Sanctum v4.3 installed, config + migration published, `personal_access_tokens` migrated.
- [x] Design system baseline: `layouts/app.blade.php` (Bootstrap 5 + jQuery + Axios CDN), `public/css/tokens.css`, `public/css/components.css`, dashboard + page stubs.
- [x] Routes converted to controller actions (AGENTS.md convention), demo protected groups preserved.
- [x] Audit: `audit_logs` migration, `AuditLog` model, `AuditLogger` service (context: actor/ip/device/source/request_id), `Auditable` trait, `config/audit.php`, login/logout entries.
- [x] Seeders: `AdminUserSeeder` (env creds), `DatabaseSeeder` (admin + supervisor + cleaner), seeded.
- [x] Docs: `docs/permission-matrix.md`, `docs/design-tokens/tokens.json`.
- [x] CI: `.github/workflows/ci.yml` (pint + migrate + test).
- [x] `.env.example` — `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `AUDIT_ENABLED`, `AUDIT_QUEUE`.
- [x] Pint clean; 27 tests green (`AuthTest`, `AuditTest`, `PasswordResetTest`, `PermissionMiddlewareTest`).

## In Progress

- Nothing.

## Next

1. Queue/scheduler: nothing scheduled yet — wire `GenerateRecurringTasks` etc. in tasks module. Dev queue = `database` (works out of box).
2. Storage: S3 disk config when evidence/images module lands (properties module).
3. API stabilization: `routes/api.php` + Sanctum token issuance at login — start with personnel module.
4. Session management UI (deferred to reports phase).

## Verified

- `php84 artisan test` — 27 passed.
- `php84 vendor/bin/pint` — clean.
- Login redirects guests; permission/role middleware blocks unauthorized.