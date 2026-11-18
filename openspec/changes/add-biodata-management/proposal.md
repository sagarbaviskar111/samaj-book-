## Why
Biodata is the core of SamajBook. Members need to create, edit and manage matrimonial biodatas (for self or family), upload photos safely, view profiles with privacy enforced, and download a watermarked PDF.

## What Changes
- 5-step create/edit form covering every `biodatas` column; shared option lists in `App\Support\BiodataOptions`.
- Status rules: new/edited biodata -> `pending`; drafts allowed. Editing an approved biodata returns it to `pending` only if name, dob, gender or photos changed.
- Photos: max 6, GD re-encode (EXIF stripped, max 1600px), public disk `biodata/{id}`, primary selection, delete.
- Privacy-enforcing photo route `biodata.photo` (blur / placeholder / viewer watermark, cached under `storage/app/private-cache`).
- Profile page `biodata.show` with contact/income gated by owner/admin/accepted interest; view counter.
- Dashboard `biodata.mine` with completeness bar, pause/resume, mark married.
- PDF download (dompdf) with viewer watermark, owner/admin/accepted only.
- Authorization via `App\Support\BiodataAccess` (no policy registration).

## Out of scope
Listing/search/interests/shortlist (Search agent), admin approval (Admin agent), reports (Feed agent).
