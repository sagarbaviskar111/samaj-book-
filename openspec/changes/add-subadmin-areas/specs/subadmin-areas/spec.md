# Sub-admin areas

### Requirement: Area isolation
A sub-admin SHALL only see and act on members, biodata, news and reports belonging to their area.

#### Scenario: Out-of-area member
- WHEN a Nagpur sub-admin opens or approves a Pune member
- THEN the response is 404 and nothing changes

### Requirement: Restricted capabilities
Sub-admins SHALL NOT access settings, audit log, sub-admin management, success stories, or block/delete/reset-password actions.

#### Scenario: Forbidden page
- WHEN a sub-admin requests /admin/settings
- THEN the response is 403

### Requirement: Area news
News created by a sub-admin SHALL have area set to their area and notify only active members of that area.

#### Scenario: Publish with notify
- WHEN a Nagpur sub-admin publishes news with notification
- THEN only active Nagpur members are notified

### Requirement: Sub-admin management
The main admin SHALL create, edit (area, status, password) and delete sub-admins.

#### Scenario: Deactivate
- WHEN the main admin sets a sub-admin to blocked
- THEN that sub-admin gets 403 on admin pages
