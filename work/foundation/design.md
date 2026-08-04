# Foundation — Design

## Middleware chain (web + api)

```
Request
 ├─ web:  StartSession → EncryptCookies → CSRF → auth → [permission | role]
 ├─ api:  Sanctum auth → rate limiting → [permission | role]
 └─ public: no auth/permission middleware
```

- `permission:1.1,2.1` = OR (any granted key).
- `['permission:4.5', 'role:1']` = AND (array of middleware).
- Grant semantics: parent key implies children (`1` ⇒ `1.1`), `*` = everything (admin).

## audit_logs

| column | type | notes |
|---|---|---|
| id | bigint PK | |
| actor_id | FK users nullable | null = system |
| action | string(50) | login, create, update, delete, approve… |
| entity_type | string(100) | model class |
| entity_id | bigint nullable | |
| before | json nullable | state before |
| after | json nullable | state after |
| ip | string(45) nullable | |
| device | string(255) nullable | UA / device id |
| source | string(20) | web, api, system |
| request_id | string(50) nullable | |
| created_at | timestamp | immutable — no updates |

Indexes: `(entity_type, entity_id)`, `(actor_id, created_at)`, `created_at`.

## File layout

```
app/
 ├─ Services/Audit/AuditLogger.php
 ├─ Support/Auditable.php        (trait)
 ├─ Models/AuditLog.php
 ├─ Http/Controllers/Auth/AuthController.php
 └─ Middleware/ (existing RBAC)
config/audit.php                  (enabled, sync-vs-queued)
```

## Delivery checklist

- [ ] Auth: login/logout + routes + admin seeder + tests.
- [ ] Sanctum wired; API token issuance in login response.
- [ ] Audit: migration, logger, trait, tests (create/update/delete/login).
- [ ] Queue: env template, scheduler file, worker docs.
- [ ] Storage: S3 disk + `public/uploads` dev fallback.
- [ ] Tokens: `docs/design-tokens/tokens.json` + CSS vars.
- [ ] CI: pint + test actions.