# Auth

## ADDED Requirements

### Requirement: Registration
New members register with name, a unique 10-digit Indian phone, password and consent; they start as pending.

#### Scenario: Valid registration
- WHEN a visitor submits valid details with consent
- THEN a user with role `user` and status `pending` is created, logged in and redirected to `account.status`

#### Scenario: Missing consent
- WHEN consent is not ticked
- THEN registration fails with a validation error

### Requirement: Login
Members log in with phone and password, throttled to 5 attempts per minute.

#### Scenario: Wrong password
- WHEN credentials are wrong
- THEN login fails with an error and no session

#### Scenario: Blocked user
- WHEN a blocked user logs in
- THEN login is refused with a message

### Requirement: Account status
#### Scenario: Active user
- WHEN an active user opens `account.status`
- THEN they are redirected to `home`

### Requirement: Profile
#### Scenario: Avatar validation
- WHEN a non-image or oversized avatar is uploaded
- THEN the update is rejected
