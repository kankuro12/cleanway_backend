# Foundation — Design

## Architecture

- Repo: `mobile/` inside this project; standalone Angular components; lazy-loaded feature routes.
- Layering: `core/` (guards, token store, API client, permissions, theme) → `features/<domain>/` → `shared/` (badge, cards, filter sheet, state components).
- No global store (YAGNI): per-screen server state + pull-to-refresh; revisit if cross-screen sync appears.

## Auth & storage

- Token: Capacitor Preferences + native Keychain/Keystore wrapper (iOS Keychain, Android EncryptedSharedPreferences).
- Login response shape: `data.user` (UserResource fields) + `data.token` (plainTextToken). Persist token only; re-fetch `/me` per launch to refresh role/status.
- Guard: no token → login route; token present → load `/me` (fail → logout). 401 interceptor on any request → same flow.

## API client

- Single `HttpService` wrapping Angular HttpClient: attaches bearer, maps `{message}` + field errors, exposes typed `getList()` honoring `meta.pagination` (infinite scroll), retries on transient failure up to 2x, respects throttle 429 (no auto-retry).
- Endpoints as typed method groups (AuthService, TaskService, AttendanceService, NotificationService, PersonnelService, PropertyService) — one source of truth per domain; DTO interfaces mirror API payloads exactly (GPS fields, shiftPayload, eventPayload shapes from the API controllers).

## Theming

- `variables.scss`: map every `--cw-*` token 1:1; Ionic component CSS vars (ion-button primary = accent, ion-card radius near-sharp, ion-badge mono style).
- Typography: bundled Archivo + IBM Plex Mono via @font-face; micro-label class reused across shared components.
- Status badge shared component: dot + mono label + tinted bg (tint values from tokens); semantic class per status key.
- Dark mode: token override only; `prefers-color-scheme` binding.

## Shell

- `AppShellComponent` owns the tab bar; tab list resolved from permissions after `/me`.
- Deep-link registry: task/{id}, incident/{id}, notification/{id} routes registered at app boot.

## Testing

- Vitest: HttpService serialization/error mapping, guard flows (token present/expired), permission service.
- Manual: login/logout on both platforms + profile render.
