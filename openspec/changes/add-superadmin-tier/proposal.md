# Add superadmin tier

## Why
The owner needs a role above the main admin who can create admins and alone sees full logs, user 360 profiles, payments and integration keys (payment gateways and similar), while everything else keeps working.

## What
- Role `superadmin` (hierarchy: superadmin > admin > subadmin > user). `User::isFullAdmin()` = admin or superadmin; `isSuperAdmin()` = superadmin only.
- Middleware alias `superadmin`; `admin` middleware now rejects any non-active admin tier.
- `/superadmin` area (`superadmin.*`): console, admins CRUD/block/reset, full logs (audit, login log, security events, CSV), 360 profile, payments (manual entry, refund, CSV), integrations (encrypted key store, payment settings).
- Tables: `login_logs`, `payments`, `integration_secrets` (values encrypted with `Crypt`).
- Audit separation: superadmin-area actions are stored with action prefix `sa.`; `admin.audit` hides superadmin actors and `sa.*`.
- `php artisan superadmin:create {phone} {name} {--password=}` is the only way to create a superadmin.
- No real gateway calls yet; `IntegrationSettings::forGateway()` is the hook for future code.
