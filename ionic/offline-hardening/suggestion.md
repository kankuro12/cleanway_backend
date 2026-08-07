# Offline & Hardening — Suggestions

- Test airplane-mode on Android + iOS from Phase 2, not Phase 8 — the outbox hooks are trivial when added early, painful later.
- Send `offline: true` on queued flushes (field exists) — backend can flag late arrivals honestly in audit trails.
- Cap evidence at the API's 10 MB limit client-side (compress before queue) — oversized photos are the most common queue poison.
- Keep failed items visible in a "Sync issues" list inside Profile — silent failures = lost field data; visible failures = fixed by the user.
- Device-permission denials are the top support ticket on field apps — the rationale sheet is not optional polish, it's a requirement.
