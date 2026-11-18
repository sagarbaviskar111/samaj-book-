## ADDED Requirements
### Requirement: Search approved biodatas
The system SHALL list only approved biodatas of the sought gender, excluding the viewer's own.

#### Scenario: Own and unapproved hidden
- WHEN a member searches for brides
- THEN own biodatas and non-approved biodatas are not listed

#### Scenario: Filters
- WHEN age, education or district filters are applied
- THEN only matching biodatas are listed

### Requirement: Match score
The system SHALL compute a 0-100 match from the viewer's expectations.

#### Scenario: Perfect match
- WHEN a candidate satisfies all expectations
- THEN the score is high (>= 90)
