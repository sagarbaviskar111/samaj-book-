# Access modes (lite / view-only / full) and on-behalf requests

## Why
Members who only registered should not see other people's full biodata. Members looking for a relative need full viewing without owning a biodata. Members must be able to send requests for a relative (cousin etc.) whose biodata they created.

## What
- `App\Support\AccessLevel::for($user)` returns `full` | `view_only` | `lite` (admins always full), memoized per request.
- Lite: search shows only photo (privacy rules apply), first name, age; filters, sort, keyword, match %, shortlist ignored/blocked server-side; detail page uses `biodata/show-lite` (no other data reaches the view) with a locked panel offering "Apna biodata banayein" or "Looking for".
- Looking-for profile (`looking_for_profiles`, routes `looking-for.*`): grants full viewing, no sending; also gives search defaults (tab, age, education, district) and a banner.
- On behalf: sender picks which of his approved, opposite-gender biodatas the request is for; `added_by` gains `cousin`, `relative`; interests whose from-biodata `added_by != self` get different notifications, a received badge and both phone numbers after acceptance.
- Seeder `DemoAccessSeeder`.
