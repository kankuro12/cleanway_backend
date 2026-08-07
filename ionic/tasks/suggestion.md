# Tasks — Suggestions

- Order evidence capture before complete in the UI flow — the backend 422 (missing checklist) is the last line of defense, not the first: pre-check locally with the same rules.
- Show `inside_geofence` + distance on the detail screen (check-in response) — cleaners on site trust the app more when it shows the radius.
- `current`/`finished` segments should map to the same status filter the web tabs use — keep one mapping table in one place (shared config), not duplicated per screen.
- For evidence, default to camera over gallery for before/after shots (context: field evidence), but keep both.
- When offline, mark actions with a visible "queued" chip so cleaners aren't surprised the status hasn't changed yet.
