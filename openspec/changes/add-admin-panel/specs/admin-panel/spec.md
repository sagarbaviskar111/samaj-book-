## ADDED Requirements

### Requirement: Admin access guard
Only users with an admin role SHALL access `/admin`.

#### Scenario: Non-admin blocked
- **WHEN** a normal member requests `/admin`
- **THEN** the response is 403

#### Scenario: Guest redirected
- **WHEN** a guest requests `/admin`
- **THEN** they are redirected to login

### Requirement: Member approval
Admin SHALL approve or reject pending members, notifying them and logging the action.

#### Scenario: Approve
- **WHEN** admin approves a pending member
- **THEN** status becomes active, an `account_approved` notification is created and an audit log row is written

#### Scenario: Reject with reason
- **WHEN** admin rejects with a reason
- **THEN** status becomes rejected, reason stored, `account_rejected` notified

### Requirement: Biodata moderation
Admin SHALL approve or reject pending biodata.

#### Scenario: Approve biodata
- **WHEN** admin approves
- **THEN** status approved, approved_at and approved_by set, owner notified `biodata_approved`

### Requirement: Content manager
Admin SHALL manage news, posters, events and success stories.

#### Scenario: Publish with notification
- **WHEN** admin creates a published item with "notify everyone"
- **THEN** every active member receives a notification

### Requirement: Report handling
Admin SHALL resolve or dismiss reports and hide posts or block users.

#### Scenario: Hide post
- **WHEN** admin hides the reported post
- **THEN** post status is hidden and the report is resolved

### Requirement: Central area scope
All admin list queries SHALL pass through `AdminScope`.

#### Scenario: Default
- **WHEN** no sub-admin exists
- **THEN** `AdminScope::area()` returns null (all areas)
