# Foundation

Objective: make the base solid so every module builds on consistent auth, RBAC, audit, queue, scheduler, storage, testing, docs.

## Scope

1. **Authentication**
   - Web session login/logout; named `login` route so `auth` middleware redirect works (currently missing).
   - `LoginController` (`POST login`, `POST logout`), `whoami`/`me` for API.
   - Seed one admin user (`role = 0`).
2. **RBAC** (done — verify only)
   - `config/permissions.php` role→grant map (`0` admin, `1` supervisor, `2` cleaner), dotted permission keys, wildcard semantics.
   - `permission` + `role` middleware aliases in `bootstrap/app.php`.
   - `User::hasPermission/hasAnyPermission/hasRole/hasAnyRole`.
   - Permission matrix doc (spec §28.6) in `docs/permission-matrix.md`.
3. **Audit framework**
   - `audit_logs` table + `AuditLogger` service + `Auditable` model trait.
   - Auto log create/update/delete; explicit calls for login/logout, approvals, corrections, exports.
4. **Queue & Scheduler**
   - Wire `QUEUE_CONNECTION` (redis prod, database dev), queue worker docs.
   - Scheduler entries in `routes/console.php`.
5. **Storage**
   - S3-compatible disk config for images/documents/exports.
6. **Design tokens**
   - `docs/design-tokens/tokens.json` per spec §20; map to CSS vars.
7. **Testing & CI**
   - Pint config, GitHub Actions (lint + test).
8. **Docs skeleton**
   - `docs/{architecture,api,decisions,testing}`.

## Permissions used

`1.1` users, `1.2` roles & permissions, `1.4` organization config, `9.1` audit view.

## Exit criteria (spec §25 Phase 0)

Login works; roles enforced; queues run; storage works; tests run in CI; audit base active.