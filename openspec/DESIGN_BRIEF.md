# Design Brief: "make it noticeably better" pass

Product: SamajBook, a Dhobi samaj matrimonial + community PWA. Audience: families, 90% on phones (Android, mid-range), many are not tech-savvy. Hindi-first, warm, trustworthy, celebratory (think shaadi invitation meets modern app). It must feel **premium, polished and alive**, not like a generic Tailwind template.

## Direction
- Palette is fixed (tokens in `resources/css/app.css`): royal indigo `ink-*`, marigold `gold-*`, `cream` background, `rose-brand` for hearts/likes. Keep it, but use it with more confidence: rich gradients on hero/cover areas, gold accents for primary celebratory actions, generous cream whitespace.
- Typography: Playfair Display for headings (`font-display`), Poppins body, Devanagari fallbacks already loaded. Establish a clear scale (page title, section title, card title, meta). Tight, confident headings; comfortable 15-16px body; never tiny (<11px) text except badges.
- Depth: layered soft shadows (`shadow-soft`, `shadow-glow`), subtle rings, glass blur on bars, rounded-2xl/3xl cards. Add gentle decorative motifs (subtle paisley/mandala SVG, marigold/diya accents) as low-opacity backgrounds, tastefully, not clip-art.
- Motion: small, purposeful transitions (hover lift, press scale, fade/slide-in on load, skeleton-ish placeholders where natural). Always respect `prefers-reduced-motion`.
- Components should feel consistent: same radii, paddings, button heights (44px min touch target), badge styles, empty states with a small illustration (inline SVG) + friendly Hindi/English line + clear CTA, consistent form field look, helpful validation messages.
- Phone first (360px wide must be perfect), then enhance for `sm`, `md`, `lg`. Desktop must look intentionally designed (multi-column, sidebars, max widths), never a stretched phone layout.
- Accessibility: contrast >= 4.5:1 for text, visible focus rings, alt text, labels.
- Performance: no new heavy libraries or images; inline SVG and CSS only. Fonts already loaded.

## Hard rules (parallel work; read `openspec/PROJECT_CONTEXT.md` too)
1. **Presentation only.** Do NOT change routes, controllers, models, migrations, validation, form field `name`s / `id`s, or HTTP methods. Do not remove any existing functionality, text-bound logic, `@csrf`/`@method`, or JS hooks (ids/classes that scripts query). You may add markup, classes, SVG, copy tweaks, small inline `<script>` for progressive enhancement.
2. Only edit the files in YOUR area (given in your task). To add reusable CSS, append to the END of `resources/css/app.css` inside a clearly named block `/* ==== <your-area> ==== */` (re-read the file right before editing; others append too). Use the `@utility` syntax (Tailwind v4) for reusable classes you want in `@apply`; plain CSS otherwise. Never rename or delete existing classes (`btn-*`, `card`, `input`, `badge-*`, `section-title`, `gradient-hero`, `glass`, ...). Upgrading what they look like is fine and encouraged only if you are the one assigned shared files.
3. Files must be written with the Write/Edit tools (no BOM). Tests use `withoutVite()`.
4. Do NOT run `npm run build`/`npm run dev` (the lead builds at the end; classes you use are picked up automatically). Do run `php artisan test` for your area to ensure nothing broke (tests assert on visible text: keep key strings such as headings/labels they look for, or update the assertion minimally if you truly reword).
5. You cannot look at the page. Be careful with Tailwind class names (v4) and valid Blade; after editing run `php artisan view:cache` (then `php artisan view:clear`) to catch Blade syntax errors.
6. Dev-only helpers must stay: `@section('below')` dev panel in `auth/login.blade.php`, `partials/dev-*.blade.php`, `<x-install-prompt />`.
7. Final reply: SHORT list of what you improved + any follow-ups. No code dumps.
