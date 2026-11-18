# Events

## ADDED Requirements

### Requirement: Registration popup candidate
The popup endpoint SHALL return the nearest published, open, not-ended event in the member's audience that the member has not registered for.

#### Scenario: Unregistered member
- WHEN an active member who is not registered requests `/events/popup`
- THEN `register` holds the nearest upcoming event and `join` is null

#### Scenario: Registered or unpublished
- WHEN the member is already registered, or the event is unpublished, ended, or belongs to another area
- THEN `register` is null

### Requirement: Event-day join popup
The endpoint SHALL return `join` for a registered member when the event starts today, has not ended and the member has not joined.

#### Scenario: Join recorded
- WHEN the member opens `events.join`
- THEN `joined_at` is stored and later popup responses omit `join`

### Requirement: Admin scoping
A sub-admin SHALL only list and edit events of their own area; new events are forced to that area.

#### Scenario: Other area
- WHEN a sub-admin opens an event of another area
- THEN the response is 404
