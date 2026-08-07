# Foundation — Suggestions

- Lock the Angular-vs-React decision here and never revisit (team familiarity + Capacitor typing favor Angular; both are fine — the plan assumes Angular).
- Do the Phase 1 spike before parallel backend work: prove Capacitor build + secure storage + /me round-trip on one real device (Android) before scaffolding everything.
- Profile screen is cheap (data already in `/me`) — build it in this phase, don't defer to a later one.
- Base URL via environment injection at build time (dev/prod), not runtime settings — avoids accidental prod writes from dev builds.
- Keep DTO interfaces auto-mirroring API payloads; add a tiny contract test (Vitest) pinning the field names — cheap insurance against backend renames.
