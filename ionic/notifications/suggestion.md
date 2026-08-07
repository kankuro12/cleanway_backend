# Notifications — Suggestions

- Send `device_name` on login (it's an accepted field) — helps admin identify which device tokens belong to which app install.
- Always include a target route in push payloads; generic "new notification" pushes without routes are the #1 source of deep-link bugs.
- Register the device token BEFORE showing the feed — otherwise early users get pushes that route nowhere.
- Keep unread count server-derived (feed meta), not locally summed — devices disagree on read state.
