# Proposal: Kundli (Gun Milan / Ashtakoot) matching, step 1

## Why
Families expect kundli milan. It must be free (pure tables, no AI/API) and private: rashi/nakshatra and the result are visible only after an interest is ACCEPTED, always with a "not 100% accurate, verify with your pandit" disclaimer.

## What
- biodatas: optional `rashi`, `nakshatra`, `nakshatra_pada` (form + server-side derivation/validation).
- `App\Services\Kundli\GunMilan` + `Tables`: classic 36-point Ashtakoot.
- `App\Support\KundliAccess`: accepted-interest gate (parties incl. on-behalf sender, admins).
- Route `interests.kundli`, view `search/kundli.blade.php`, buttons on accepted cards and biodata.show, kundli info card.
- `DemoKundliSeeder`.
