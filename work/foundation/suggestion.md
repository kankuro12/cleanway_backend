# Foundation — Suggestions

- **Auth**: use plain `AuthController` with session guard, no Breeze — keeps dependency count low; add Livewire later if needed.
- **Audit**: implement as trait + queued writer in prod; keep sync in tests so assertions are deterministic.
- **Seeding**: admin seeder reads credentials from `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`), never hardcodes.
- **RBAC**: keep config-based (not spatie package) — 3 static roles fit config; revisit only if per-user custom permissions become a requirement.
- **Queue**: start with `database` connection in dev to avoid Redis dependency in local env; document `redis` for prod.
- **Timezone**: store UTC, convert in a presentation helper — do it now before modules write timestamps.
- **Helper access**: keep controllers thin — permission checks via middleware; scope queries via a `ScopeForRole` trait/service shared by web + API.