# Add area-wise sub-admins

## Why
One admin cannot moderate every district. Local volunteers should approve members/biodata, post local news and handle reports, but only for their own area.

## What
- New nullable `users.admin_area` (a district name) and role `subadmin`.
- `AdminScope::area()` returns the sub-admin's area; all admin queries (users, biodatas, news, reports, stories) filter through it. Out-of-area records return 404.
- Main admin only (`admin.full` middleware): settings, audit log, sub-admin CRUD, success stories, block/unblock/reset-password/delete users, biodata verify/status/delete, block user from report.
- Sub-admin news is auto-tagged with their area; publish notifications go only to active members of that area.
- Sidebar and dashboard adapt ("SUB-ADMIN · Nagpur"); audit log shows sub-admin name and area.
- Demo seeder `DemoSubAdminSeeder` (Nagpur 9888888801, Pune 9888888802, password demo12345).
