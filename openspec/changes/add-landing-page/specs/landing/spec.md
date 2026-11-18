# Landing capability

### Requirement: Public landing page
The system SHALL serve a public landing page at `/`.

#### Scenario: Guest visits
- WHEN a guest opens `/`
- THEN the page renders with 200 and shows published landing news items

#### Scenario: Active member visits
- WHEN an active member opens `/`
- THEN they are redirected to `home`

#### Scenario: Admin visits
- WHEN an admin opens `/`
- THEN they are redirected to `admin.dashboard`

### Requirement: Static policy pages
The system SHALL serve terms, privacy, about and faq pages publicly.

#### Scenario: Guest opens privacy
- WHEN a guest opens `/privacy`
- THEN the page renders with 200 and mentions DPDP Act 2023
