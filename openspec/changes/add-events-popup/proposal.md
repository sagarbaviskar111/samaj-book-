# Proposal: Events with registration and join popups

## Why
Admins and sub-admins want to announce events (sammelan, meetings) and make sure members notice them. A popup that asks members to register, and a repeating "Join now" popup on the event day, grabs attention.

## What
- Admin and sub-admin (own area only) manage events at `/admin/events` (CRUD, registrants list, CSV export, optional notification).
- Members see Events in the nav, browse upcoming/my/past events, register and unregister.
- Global popup component driven by `GET /events/popup` (cached 30s per user).
  - Registration popup: once per visit, again after leaving for 5+ minutes. "Baad me" dismisses for the visit.
  - Event-day join popup: for registered members, repeated every `reminder_interval_min` minutes until they click Join.
- New tables `events` and `event_registrations`.

## Out of scope
Push notifications for reminders, payments, capacity limits.
