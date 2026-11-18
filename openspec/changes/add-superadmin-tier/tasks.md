## 1. Role model
- [x] User helpers (isFullAdmin, roleLabel), EnsureSuperAdmin, EnsureAdmin status check, EnsureFullAdmin allows superadmin
- [x] Views refactored from isSuperAdmin to isFullAdmin; role labels fixed in layout
## 2. Data
- [x] Migrations login_logs, payments, integration_secrets
- [x] LoginLog listener (Login, Failed, Logout, dev_login), Failed dispatched from LoginController
## 3. Superadmin area
- [x] Admins, logs, 360 profile, payments, integrations, dashboard (+ permission matrix)
- [x] superadmin:create command
## 4. Tests
- [x] tests/Feature/Superadmin/* (access matrix, admins, logs, payments, integrations, profile, command)
