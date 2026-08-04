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

## Laravel Admin Design System

- Use the Laravel Admin Design System whenever building or editing Blade views, layouts, partials, pages, or any visual UI work.
- Base visual work on Bootstrap 5 + jQuery + Axios loaded via CDN, not npm/Vite. Do not add a second Bootstrap/jQuery/Axios bundle.
- Use `tokens.css` for theme overrides and `components.css` for the admin shell and utility styles; do not hardcode new colors or spacing in markup.
- Extend `layouts.app` for every new page and reuse plain Bootstrap markup for buttons, cards, tables, forms, badges, alerts, nav, and status rails.
- Only create a Blade component when it adds behavior or logic beyond class composition. Otherwise, keep markup plain Bootstrap.
- Use jQuery for interactive behavior and Axios for AJAX calls. Avoid vanilla `document.querySelector`/`addEventListener` and avoid `fetch`/`$.ajax`.
- Check existing `assets/css/`, `assets/js/`, and `assets/blade/components/` before adding new styles or components; these files are the system source of truth.
