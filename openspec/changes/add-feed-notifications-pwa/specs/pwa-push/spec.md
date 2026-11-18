## ADDED Requirements
### Requirement: Installable PWA
The app SHALL serve a manifest and service worker with an offline fallback.
#### Scenario: Offline
- WHEN the network is down on navigation
- THEN offline.html is shown

### Requirement: Web Push
Every database notification SHALL trigger a push to the user's subscriptions when VAPID keys exist; otherwise it no-ops.
#### Scenario: Subscribe
- WHEN a member posts a subscription to push.subscribe
- THEN it is stored for that user
