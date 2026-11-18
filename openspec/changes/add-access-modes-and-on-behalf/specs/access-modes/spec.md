# Access modes

### Requirement: Lite members see only photo, first name and age
#### Scenario: Lite search
- WHEN a member without a submitted biodata or looking-for profile opens /biodata with filters
- THEN filters are ignored and only photo, first name and age are rendered

#### Scenario: Lite detail
- WHEN a lite member opens a biodata
- THEN only photo, first name, age and the two unlock options are shown

### Requirement: View-only members see everything but cannot send
#### Scenario: View-only
- WHEN a member has a looking-for profile and no submitted biodata
- THEN full details are visible, interest creation is refused and a create-biodata prompt is shown

### Requirement: Requests on behalf of relatives
#### Scenario: Sender selection
- WHEN a member opens a profile
- THEN only his approved opposite-gender biodatas are offered as "Kiske liye"

#### Scenario: Receiver sees on-behalf context
- WHEN the interest's from-biodata has added_by other than self
- THEN notification, badge and accepted contact card name the sender and relation and show both phones
