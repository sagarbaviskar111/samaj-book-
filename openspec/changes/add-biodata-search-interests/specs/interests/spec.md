## ADDED Requirements
### Requirement: Send interest
A member SHALL send an interest from their own approved biodata to an approved biodata of the opposite gender, once per pair, within a daily limit.

#### Scenario: Duplicate rejected
- WHEN an interest already exists for the pair
- THEN a new send is refused

#### Scenario: Daily limit
- WHEN the daily limit is reached
- THEN further sends are refused

### Requirement: Respond
Only the receiving user SHALL accept or reject; the sender is notified.

#### Scenario: Wrong user
- WHEN another user tries to respond
- THEN the response is forbidden

### Requirement: Expiry
Sent interests past expires_at SHALL be marked expired by `interests:expire`.

#### Scenario: Expire
- WHEN the command runs
- THEN overdue sent interests become expired

### Requirement: Contact reveal
Contact phone SHALL be shown only for accepted interests.
