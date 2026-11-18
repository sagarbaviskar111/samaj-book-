## ADDED Requirements
### Requirement: Visible feed
The feed SHALL show only visible posts that the viewer may see (members, or city-visibility posts from the viewer's city).
#### Scenario: Hidden post
- WHEN a post has status hidden
- THEN it does not appear in the feed

#### Scenario: City post
- WHEN a post has visibility city and the author is in another city
- THEN the viewer does not see it

### Requirement: Likes and comments
Like toggling SHALL maintain likes_count and notify the owner at most once per liker; comments maintain comments_count.
#### Scenario: Like twice
- WHEN a user likes, unlikes and likes again
- THEN the owner has only one like notification

### Requirement: Reports
A reporter SHALL NOT create a second open report for the same target.
#### Scenario: Duplicate report
- WHEN the same user reports the same post twice
- THEN only one open report exists
