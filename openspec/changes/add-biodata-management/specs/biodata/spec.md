## ADDED Requirements

### Requirement: Create and edit biodata
Members SHALL create biodatas with legal-age validation (female 18+, male 21+) and edit only their own.

#### Scenario: Underage rejected
- **WHEN** a member submits a male biodata with dob making him 20
- **THEN** validation fails on `dob`

#### Scenario: Owner-only edit
- **WHEN** another member requests the edit page
- **THEN** the response is 403

### Requirement: Visibility
Only approved biodatas SHALL be visible to non-owners; admins and owners see any status.

#### Scenario: Pending hidden
- **WHEN** a non-owner opens a pending biodata
- **THEN** the response is 404

### Requirement: Contact gating
Contact phone SHALL be shown only to owner, admin, or a viewer with an accepted interest in either direction.

#### Scenario: Before accept
- **WHEN** a member views an approved biodata with no accepted interest
- **THEN** the phone number is not in the page

### Requirement: Photos
A biodata SHALL hold at most 6 photos and non-owner views SHALL go through `biodata.photo`, honoring `photo_privacy`.

#### Scenario: Limit
- **WHEN** a 7th photo is uploaded
- **THEN** it is rejected

#### Scenario: Blur
- **WHEN** privacy is `blur_until_accepted` and no accepted interest exists
- **THEN** the route returns a blurred image response
