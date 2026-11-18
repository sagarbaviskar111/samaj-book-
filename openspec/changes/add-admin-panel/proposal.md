## Why
The platform needs a single-admin back office to approve members and biodata, moderate reports, publish news/events and watch key numbers, usable on phone and desktop.

## What Changes
- New `/admin` area (`admin.*` routes, `auth` + `admin` middleware) with responsive layout.
- Dashboard, members, biodata moderation, news/events/stories CMS, reports, settings, audit log, admin password.
- `AdminScope` service centralises the area restriction (null = all areas) so sub-admins can be added later.
- Every moderation action writes an `AuditLog` and notifies the member via `GeneralNotification`.

## Out of scope
Sub-admins by area, OTP, analytics beyond dashboard, push notifications.
