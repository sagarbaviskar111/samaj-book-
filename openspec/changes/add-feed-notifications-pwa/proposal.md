## Why
Members need a community feed (posts, likes, comments, reports), in-app notifications, and an installable PWA with Web Push so the app feels like a native social app. No chat.

## What Changes
- Home feed with stories-style strip, composer, posts, likes, comments, reports.
- Notification centre, per-type preferences, mark read.
- PWA manifest, service worker, offline page, icons, install prompt.
- Web Push via minishlink/web-push, VAPID keys command, listener on database notifications.

## Impact
New routes in routes/modules/feed.php and notifications.php; new controllers, views, PushService, listener; no schema changes.
