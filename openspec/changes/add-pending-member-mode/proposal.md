## Why
New members log in immediately but are not yet verified as samaj members. Until an admin approves them they must see only a read-only news feed, never rishta/biodata/profiles or events.

## What Changes
- `User::isPending()`; `EnsureActive` lets pending users reach only `EnsureActive::PENDING_ALLOWED` routes (others: redirect to `home` with `pending_notice`, or 403 JSON `Admin approval baaki hai`).
- Register / login / dev-login send pending users to `home`; rejected and blocked keep the `account.status` behaviour.
- Feed is read-only for pending users: no composer, like, comment, report, delete; only `members` posts; story strip limited to `news` and `poster`; no events or biodata data queried or rendered.
- Global approval banner, reduced top/bottom nav, no event popup, simplified profile page.
- `news.show` returns 404 for event items to pending users.

## Impact
No schema change. Admin approval (status `active`) restores full access immediately.
