# Offline & Hardening

Objective: offline tolerance for field writes (transitions, evidence, incidents, clock), runtime permissions UX, app assets, release readiness. Runs last but designed from Phase 2 — offline hooks are in-place from the start.

## Scope

1. **Outbox queue** — write actions (check-in/out, evidence, complete, incident, clock in/out/breaks) enqueue when offline: payload + GPS + device_timestamp captured at action time; flush on reconnect in order; per-item status (queued / uploading / done / failed).
2. **Conflict handling** — 422/403 on flush → item marked failed w/ server message surfaced (never silently dropped); user resolves by retrying/editing; illegal-transition failures don't deadlock the queue (skip + surface).
3. **Network awareness** — Capacitor Network events; banner "offline — changes queued"; disabled actions that must be online (filters/search, reports).
4. **Read cache** — last-loaded list/detail pages cached for offline viewing (tasks, shifts); evidence file paths held locally until upload confirms.
5. **Runtime permissions** — location (foreground; background only if needed later), camera, notifications: rationale screens before OS prompt, denied-state UI w/ settings deep link.
6. **Assets & release** — icons/splash per platform (design-system styled), app id/versioning scheme, debug/prod API envs, signing config, store checklist, smoke matrix (Android 12+, iOS 16+, airplane mode, fresh install, upgrade install).
7. **Tests** — Vitest: queue ordering, retry/backoff, conflict resolution, dedupe; manual matrix below.

## APIs used

All write endpoints (see master inventory); network events; no new backend needed — `offline` + `device_timestamp` fields exist precisely for this (GPS_RULES includes them).

## Exit criteria

Airplane-mode: full task completion (check-in + evidence + complete + submit path) syncs with zero data loss; clock-out offline works; queue failures surface user-resolvable messages; store-ready build on both platforms.
