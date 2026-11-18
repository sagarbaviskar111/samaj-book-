# Proposal: biodata share link + redesigned PDF

## Why
Members want to share their biodata as a link and as a PDF. The link must not become a public page; the PDF must render Hindi/Marathi correctly and look good.

## What
- `/b/{token}` deep link to the normal biodata page (login + admin approval still required); generic OG tags only.
- Owner Share sheet (copy, WhatsApp, native share, PDF with/without phone, reset/toggle link).
- PDF engine switched from dompdf to mPDF (dompdf cannot shape Devanagari); new design with a very light centred logo watermark on every page.
- Migration `2026_11_23_000001`: `biodatas.share_token`, `share_enabled`.
