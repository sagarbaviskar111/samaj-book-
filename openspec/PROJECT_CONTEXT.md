# Samaj Book: Project Context (read before any work)

Dhobi samaj matrimonial + community platform. **Mobile-first PWA**, Hindi-first UI (English labels are fine, keep text short; wrap UI strings in `__()` where easy). Plan doc: `E:\Samaj book\PLAN.md`.

## Stack
Laravel 13, PHP 8.4, MySQL in prod (dev currently SQLite via `.env`; write DB-agnostic code, no raw MySQL-only SQL), Blade + Tailwind v4 (Vite), small vanilla JS or Alpine-free JS (no extra heavy frameworks; Alpine via `npm i alpinejs` is OK if one agent adds it to `resources/js/app.js` carefully, see rules).
App root: `E:\Samaj book\app`. Run commands from there. Composer: `C:\Users\sagar\tools\composer.bat`.

## Already done by the lead (DON'T redo, just use)
- Migrations for ALL tables (`database/migrations/2026_11_16_000001_create_samaj_tables.php`, users table extended, notifications table). **Do not edit existing migrations. If you truly need a schema change, add a NEW migration file with your own unique timestamp prefix, and mention it in your report.**
- Models in `app/Models` (User, Biodata, BiodataPhoto, Interest, Shortlist, Post, Like, Comment, NewsItem, SuccessStory, Report, PushSubscription, AuditLog, Setting). Read them first. Small additions (scopes/relations) to a model are OK if you only ADD.
- Middleware aliases: `admin` (admin/subadmin only), `active` (only approved members). Use `['auth','active']` for the member area and `['auth','admin']` for admin.
- Shared in-app notification: `App\Notifications\GeneralNotification(type, title, body, url)` -> `$user->notify(...)`. Uses Laravel database notifications.
- Design system: `resources/css/app.css` (tokens + classes `btn-primary, btn-gold, btn-ghost, btn-danger, btn-sm, card, card-pad, input, field-label, field-error, badge-ok/warn/bad/ink, section-title, gradient-hero, glass`). Palette: `ink-*` (royal indigo/violet), `gold-*`, `cream`, `rose-brand`. Fonts: Poppins + Playfair Display (+ Devanagari). **Reuse these; stay consistent.**
- Layouts: `layouts/base.blade.php`, `layouts/app.blade.php` (member shell with top bar + bottom nav; use `@extends('layouts.app') @section('content')`). The bottom nav and top bar link to these route names, which MUST exist once all modules land: `home` (feed), `biodata.index`, `posts.create`, `notifications.index`, `profile.show`, `account.status`, plus `login`, `register`, `logout`.
- Single admin for now: seeded phone `9999999999`, password `admin12345` (role `admin`). Sub-admins by area come later, so keep admin queries centralized in a way that an `area` filter can be added later (e.g., a scope/helper), but DO NOT build sub-admin now.
- Login is by **phone + password** for now (OTP is a later stub: design the code so an OTP step can be inserted).

## Parallel-work rules (IMPORTANT, many agents work at the same time)
1. **Only touch files in your own area** (listed in your task). Never edit another agent's files, `routes/web.php`, `bootstrap/app.php`, `config/*`, or existing migrations.
2. Routes go ONLY in `routes/modules/<yourmodule>.php` (auto-loaded by `routes/web.php`). Controllers in `app/Http/Controllers/<YourArea>/`, views in `resources/views/<yourarea>/`.
3. Shared files you may need to touch (`resources/js/app.js`, `resources/css/app.css`, `app/Models/*`): **append-only, tiny, and re-read the file right before editing** because others may have changed it.
4. **File encoding: use the Write/Edit tools. NEVER write PHP with PowerShell `Set-Content -Encoding utf8` (adds a BOM and breaks PHP).**
5. Don't run `migrate:fresh` or delete the database (others use it). Use `php artisan migrate` only if you added a migration. For testing use feature tests with `RefreshDatabase` (they use an in-memory/sqlite DB) in `tests/Feature/<YourArea>/`.
6. Don't run `npm run dev` / `npm run build` (the lead builds). You may run `php artisan test --filter=<YourArea>` and `php artisan route:list`. If a route name from another module doesn't exist yet in your tests, guard with `Route::has()` in views.
7. **OpenSpec:** the repo is initialized (`openspec/`). Before coding, create ONE change folder `openspec/changes/<your-change-id>/` with `proposal.md` (why/what), `tasks.md` (checklist, tick items `[x]` as you finish), and `specs/<capability>/spec.md` (requirements with `#### Scenario:` blocks, WHEN/THEN). Read `openspec/config.yaml` and `.claude/skills/openspec-propose/SKILL.md` for the exact format. Keep these concise.
8. Quality bar: validation via FormRequests, authorization checks (a user can only edit their own biodata/posts), N+1 safe (`with()`), paginated lists, CSRF, mobile-first responsive UI that looks premium (rounded cards, soft shadows, gradients, good spacing, big tap targets, empty states, loading-friendly). No placeholder lorem unless it's seed/demo data.
9. When done, reply with a SHORT report: files created, routes added, how to test, any gaps/assumptions. Do not paste code.
