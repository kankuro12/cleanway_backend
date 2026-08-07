# Foundation

Objective: scaffold the app, port the design system, working auth, role-aware shell, typed API client, permissions runtime. Everything else builds on this.

## Scope

1. **Scaffold** — Ionic 8 + Angular 18 (standalone components) + Capacitor 7, TypeScript strict, `mobile/` subfolder in this repo; android + ios platforms added at kickoff.
2. **Theming** — port `tokens.css` → `variables.scss` (navy `--cw-ink-900`, safety-orange `--cw-accent` w/ dark text, `--cw-*-tint` semantic tints, blueprint-grid canvas, Archivo 900 display + IBM Plex Mono micro-labels, bundled fonts); Ionic component styling via CSS vars only; `.dark` token swap; reduced-motion respected.
3. **Auth** — login screen (email + password, matching web auth layout); token via Sanctum bearer; secure storage in OS Keychain/Keystore (never plain storage); logout deletes token server-side + locally; 401 interceptor → forced re-login; 429 throttle surfaced.
4. **Shell** — `ion-tabs` per role (tabs table in master plan); guard loads `/me` first, derives role + permission set; profile tab (name, role, employee_no, branch/team, phone, employment_type, skills, status).
5. **API client** — typed services per domain (auth, tasks, attendance, notifications, personnel, properties); shared pagination helper (`meta.pagination`); unified error handling (422 `{message}`, 403, 429); base URL per env; request logging in dev.
6. **Permissions runtime** — service holding the user's permission keys; `can('4.9')`-style checks drive tab visibility and action-strip items; no logic gating — server enforces.

## APIs used

`POST /auth/login`, `POST /auth/logout`, `GET /me` — full field list in master plan inventory.

## Forms

Login: email, password (credentials-error surfaced verbatim from API).

## Exit criteria

Login/logout round-trip on both platforms; token survives app restart; role-correct tabs render; two screens show themed states (loading/empty/error); `/me` profile renders all resource fields.
