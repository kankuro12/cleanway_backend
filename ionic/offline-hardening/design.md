# Offline & Hardening — Design

## Outbox

- Local store: Capacitor Preferences (JSON records) — not SQLite (YAGNI; queue is small: writes only). Upgrade path noted in future.md.
- Record: {id, endpoint, method, payload, files?[path], captured_at, device_timestamp, status, attempts, last_error}.
- Flush: on `network.online` + on app foreground; sequential per item; exponential backoff (2/4/8s, cap 60s) on transient failures; 4xx → failed-with-message, never retry automatically; 5xx/timeout → retry.
- Ordering: FIFO per user intent; a failed item does not block later items (server-side validation is per-request).
- GPS integrity: lat/lng/accuracy/mock flag captured at action time into the record — never re-derived at flush.

## Optimistic UI

- Queued actions show "QUEUED" chip on the affected entity; the local entity state does not pretend the server accepted it (no fake status flips) — surface: "submitted when back online".

## Permissions UX

- One `PermissionGate` service: check → rationale sheet (why) → OS prompt → denied → explain + open settings (native intent). Permission state cached per session; re-check on foreground (user may grant in settings).

## Assets

- Icons/splash: generated from design tokens (navy + orange), sized per Capacitor asset spec; dark/light splash variants.

## Release

- Env: `environment.ts` (prod) vs dev override via `--configuration`; base URL + API prefix.
- Versioning: app version + build number bumped per release; changelog per module landing.
- Smoke matrix checklist (in repo, `docs/smoke-matrix.md`): per platform × {fresh install, upgrade, airplane offline, no-permission denial, slow network}.

## Testing

- Vitest: outbox ordering/dedupe/backoff/failure states (pure logic — no Capacitor), optimistic chip logic, permission state transitions.
- Manual: the full airplane-mode scenario per platform before each phase sign-off.
