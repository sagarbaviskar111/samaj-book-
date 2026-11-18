# Superadmin tier

## Permission matrix
| Capability | superadmin | admin | sub-admin |
|---|---|---|---|
| Members, biodata, reports, news, events | all areas | all areas | own area |
| Block/delete members, verify, settings, stories, sub-admins | yes | yes | no |
| Create/manage admins | yes | no | no |
| Payments | yes | no | no |
| Integrations and API keys | yes | no | no |
| Login logs, security events | yes | no | no |
| Audit log | everything | admin and sub-admin actions only | no |
| 360 user profile | yes | no | no |

### Requirement: Superadmin-only area
Every `/superadmin/*` route SHALL require an active user with role superadmin.

#### Scenario: Other roles
- WHEN a guest, member, sub-admin or admin requests a superadmin page
- THEN a guest is redirected to login and the others get 403

### Requirement: Admin management
A superadmin SHALL create, edit, block, unblock, reset the password of and delete users with role admin, never a superadmin, and every action SHALL be audit-logged.

#### Scenario: Blocked admin
- WHEN a superadmin blocks an admin
- THEN that admin gets 403 on the next admin request

### Requirement: Superadmin creation
A superadmin SHALL only be creatable with `php artisan superadmin:create`.

### Requirement: Log separation
Admins SHALL NOT see audit entries made by superadmins, `sa.*` actions, or login logs.

#### Scenario: Admin audit page
- WHEN an admin opens the audit log
- THEN superadmin entries are absent

### Requirement: Login logs
Login, failed login, logout and dev login SHALL be recorded in `login_logs` without passwords.

### Requirement: Encrypted integration secrets
Integration values SHALL be stored encrypted, shown only masked, kept when the input is left blank, and never written to the audit log.

#### Scenario: Update secret
- WHEN the superadmin saves a new Razorpay secret
- THEN the DB value differs from the plaintext, the page shows only the last 4 characters, and the audit note lists the field name only

### Requirement: Payments
The superadmin SHALL list, filter, record manual, refund and export payments with amounts shown as Indian-grouped rupees.
