## Build Plan

- Master build plan: `work/plan.md` (links to per-module subfolders with plan/current/future/suggestion/design files). Consult before starting any module; update the module's `current.md` after completing work.

## Agent Runtime Notes

- Use `php84` instead of `php` when running PHP CLI commands in this workspace.
- Use `php84 composer.phar` instead of the global `composer` command (Windows PowerShell environments may require `npm.cmd` for npm).

## Development Conventions

- Routes: every route MUST use a controller action (no inline route closures). Define the controller and method explicitly (e.g. `Route::get(..., [Controller::class, 'method'])`).
- Controller helpers: do not call ad-hoc helper functions inside controllers. If shared logic is required, create a dedicated helper/service file and import/use it for reusability.
- Migrations & models: when adding a migration that changes a model's persisted fields, update the model's `$fillable`, `$casts`, and any relationship methods as needed.
- Cached settings: for frequently used configuration or reference data, cache aggressively and invalidate appropriately — avoid repeated DB reads for the same data.
- Minimal queries: do not pull unnecessary data from the database; select only required columns and eager-load only needed relations.
- Transactions: wrap all create, update, and delete operations that affect multiple records or could leave the system in an inconsistent state inside database transactions.
- API docs: `ionic/api.md` is the single reference for all API routes, request fields, and response shapes. Any change to `routes/api.php`, API controllers (validation rules or response payloads), API form requests, or API resources MUST be mirrored in `ionic/api.md` in the same change — new endpoints, fields, enums, or status codes included.

## Laravel Admin Design System

- Use the Laravel Admin Design System whenever building or editing Blade views, layouts, partials, pages, or any visual UI work.
- Base visual work on Bootstrap 5 + jQuery + Axios loaded via CDN, not npm/Vite. Do not add a second Bootstrap/jQuery/Axios bundle.
- Use `tokens.css` for theme overrides and `components.css` for the admin shell and utility styles; do not hardcode new colors or spacing in markup.
- Extend `layouts.app` for every new page and reuse plain Bootstrap markup for buttons, cards, tables, forms, badges, alerts, nav, and status rails.
- Only create a Blade component when it adds behavior or logic beyond class composition. Otherwise, keep markup plain Bootstrap.
- Use jQuery for interactive behavior and Axios for AJAX calls. Avoid vanilla `document.querySelector`/`addEventListener` and avoid `fetch`/`$.ajax`.
- Check existing `public/css/`, `public/js/`, and `resources/views/` before adding new styles or components; `tokens.css` + `components.css` are the system source of truth.

### Visual direction — "field-ops industrial"

The design language is an industrial operations console: dispatch-navy shells with safety-orange accent, blueprint-grid canvases, near-sharp corners, mono uppercase micro-labels. Follow it — do not drift to generic admin themes.

- **Typography**: `Archivo` (900-weight display for headings/stat numerals, tight `-0.02em` tracking) + `IBM Plex Mono` for micro-labels, table headers, badges, clocks, and IDs. No other fonts. Sizes: micro-labels 11px mono uppercase `letter-spacing .12em`; body 15px `line-height 1.6`.
- **Color**: tokens only (`--cw-*`). Navy `--cw-ink-900` for sidebar/panels, `--cw-accent` safety-orange for primary actions (dark text on orange — never white). Semantic tints (`--cw-*-tint` + deep text) for status. All values live in `tokens.css`; `.dark` redefines tokens, never restyles components.
- **Status**: use `status-badge` (dot + mono label, tinted bg) — meaning must never rely on color alone.
- **Layout**: pages open with an eyebrow (`<span class="eyebrow">Section · Sub</span>`) above one `<h1>`-equivalent page title; section content in `.card` with mono `.card-header`; lists in `.table` with mono uppercase headers. Add `reveal` classes with staggered `--d` delays on page load; respect `prefers-reduced-motion` (handled globally).
- **Accessibility (mandatory)**: real `<label>`s on all inputs (visually-hidden labels allowed), native buttons/links, one h1 per page, visible focus (orange outline — never remove default outline), body-text contrast ≥ 4.5:1 (orange text on white fails — use `--cw-accent-deep` instead), reflow to single column ≥ 320px, `alt` on every image, landmarks (`header`/`nav`/`main`/`footer`).
- **Auth pages**: standalone (no `layouts.app`) — use `auth-*` classes, split-screen on `lg`+ (navy brand panel + form side).
