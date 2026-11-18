# Biodata share and PDF

### Requirement: Share link is only a deep link
#### Scenario: Guest opens a link
- WHEN a guest opens /b/{token} of an approved, enabled biodata
- THEN they are redirected to login with a hint and the biodata page is remembered as intended URL

#### Scenario: Unavailable link
- WHEN the token is unknown, disabled, or the biodata is not approved
- THEN a 404 page without any personal data is shown

#### Scenario: Member opens a link
- WHEN an active member or admin opens it THEN they land on the normal biodata page; pending members are bounced home

### Requirement: Owner controls the link
#### Scenario: Reset
- WHEN the owner resets the link THEN the old token stops working

### Requirement: PDF
#### Scenario: Contact hidden
- WHEN ?contact=0 is passed THEN the phone number is not in the PDF
#### Scenario: Kundli
- THEN rashi/nakshatra appear only in the owner's own PDF
