# Kundli matching

### Requirement: Kundli data is private until acceptance
#### Scenario: Before acceptance
- WHEN a member views another biodata with no accepted interest between them
- THEN rashi and nakshatra are not rendered anywhere (show, search, lite)

#### Scenario: After acceptance
- WHEN an interest between the two profiles is accepted
- THEN both parties (including an on-behalf sender) see the values and a "Kundli milan dekhein" button; admins and owners always can

### Requirement: Gun Milan result
#### Scenario: Complete data
- WHEN both sides have rashi and nakshatra
- THEN 8 kootas, a total out of 36, a band, dosha flags and the disclaimer are shown

#### Scenario: Missing data
- WHEN a side lacks rashi or nakshatra
- THEN a friendly state names the missing side; its owner gets an edit link

### Requirement: Server-authoritative rashi
#### Scenario: Contradiction
- WHEN nakshatra+pada imply a different rashi than submitted
- THEN validation fails; with nakshatra+pada and no rashi the rashi is derived
