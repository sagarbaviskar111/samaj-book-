## ADDED Requirements

### Requirement: Pending members get a read-only feed only
A member with status `pending` SHALL be able to log in and see only the read-only feed, notifications, own profile and account status.

#### Scenario: Blocked route
- WHEN a pending member opens biodata, interests, shortlist, events, looking-for or post creation
- THEN they are redirected to `home` with an approval notice (JSON requests get 403 `Admin approval baaki hai`)

#### Scenario: Feed content
- WHEN a pending member opens `home`
- THEN posts, news and posters are shown without any interaction controls, event items and biodata teasers are absent, and an approval banner is visible

#### Scenario: Approval
- WHEN an admin approves the member
- THEN the member immediately has full access

#### Scenario: Rejected or blocked
- WHEN a rejected member requests any protected page
- THEN they are redirected to `account.status`
