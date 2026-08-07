# Notifications — Design

## Components

- `NotificationItem` — type icon (mono), title, body (2-line clamp), relative time, unread dot (never color-only — dot + "UNREAD" micro-label pattern per design system).
- `PushRouter` — central mapping from notification `type`/`payload` → route; unknown types fall back to feed.

## State & data

- Feed: two segments, each paginated independently (`read` param); cache per segment; pull-to-refresh.
- Read mutation: optimistic check + POST; rollback on error.
- Badge: unread count fetched with each feed load; app-icon badge via Capacitor (where supported).

## Push plumbing

- Firebase Messaging (backend infra exists — `../work/firebase/`); token refresh handled (FCM callback) → re-POST device.
- Foreground messages: present in-app banner (tap → route), don't auto-mark read.
- Background/killed: notification tap → PushRouter after auth guard resolves.

## Testing

- Vitest: PushRouter mapping table, segment param serialization, optimistic read rollback.
- Manual: receive push in all three app states per platform; deep link to task detail.
