# CleanWay Login Design

## Status

Approved design direction: refined field-operations split screen with dashboard-aligned blue accents.

## Goal

Make the CleanWay Ops login feel professional, deliberate, and easier to scan without changing authentication behavior or expanding the work beyond the login screen.

## Scope

### In scope

- Refine the standalone login page composition for desktop and mobile.
- Retain the desktop split-screen layout with a dispatch-navy brand panel and a light form side.
- Use restrained field-operations visual cues: blueprint grid, thin dashboard-blue rule, mono operational labels, and strong Archivo display type.
- Simplify the form surface so it reads as one focused sign-in area rather than a generic Bootstrap card inside a page.
- Tighten the supporting panel copy while preserving its meaning.
- Preserve server-rendered validation, remember-me, forgot-password navigation, password visibility toggle, CSRF protection, and the existing login route.
- Keep the page accessible at keyboard and mobile widths.

### Out of scope

- Forgot-password and reset-password page redesign.
- Controller, route, middleware, session, or API changes.
- Changes to the global primary palette used by the rest of the application.
- New packages, frontend libraries, or a new component library; existing CDN conventions may be used.

## Visual direction

The login uses a calm industrial operations-console aesthetic:

- The left panel uses `--cw-ink-900` and carries the brand story.
- The right side uses the existing light canvas and surface tokens with generous whitespace.
- Login-only auth aliases (`--cw-auth-accent`, hover/active variants, ink, tint, and deep text) reference the dashboard’s existing blue primary tokens, keeping the rest of the application’s palette unchanged.
- Dashboard blue is used consistently for the primary action, active operational details, focus outlines, and the thin panel rule.
- Archivo remains the display face for the brand and page title. IBM Plex Mono remains the face for uppercase eyebrows, tags, and micro-copy.
- Decorative treatment is limited to a low-contrast blueprint grid and restrained depth. No large glow, heavy shadow, or competing gradients.

## Composition

### Desktop (`lg` and above)

- Maintain a two-column full-height shell.
- The navy brand panel contains:
  1. CleanWay logo lockup at the top.
  2. A compact operations eyebrow.
  3. One `h1` headline.
  4. Three short capability points with consistent icon alignment.
  5. A thin dashboard-blue rule near the lower edge.
- The form side centers a single readable content column with a maximum width of approximately 430px.
- The form uses a subtle surface boundary or top accent, not a heavy nested card/shadow treatment.

### Mobile

- Collapse to one column at the existing `lg` breakpoint.
- Show a compact CleanWay lockup above the form.
- Preserve the same title, field order, action order, and touch-friendly target sizes.
- Reflow cleanly at widths down to 320px with no horizontal scrolling.

## Content

Use the following tightened copy:

- Panel headline: “Clean crews. Verified sites. On schedule.”
- Capability point: “Location-aware attendance”
- Capability point: “Photo evidence on every task”
- Capability point: “Audited approvals end to end”
- Form eyebrow: “Authorized personnel”
- Form title: “Sign in to CleanWay”
- Form support line: “Use your work account to continue.”
- Primary action: “Sign in”
- Footer: “CleanWay Ops · internal use only”

The logo remains the existing `logo.jpg` asset and keeps descriptive alternative text where it is meaningful. The mobile lockup remains decorative when the desktop panel is hidden.

## Interaction and states

- Keep the password visibility toggle as a native button with a minimum 44px touch target.
- Implement the toggle through the project’s jQuery convention on the standalone page.
- Keep the existing email/password autocomplete attributes and autofocus behavior.
- Keep the remember-me checkbox and forgot-password link in the same functional row, allowing the row to wrap safely on narrow screens.
- Preserve the server-rendered general error alert and style it as a clear, high-contrast error state.
- Preserve visible focus outlines using `--cw-auth-accent`, matching the dashboard blue.
- Do not add client-side authentication logic or change form submission behavior.

## Accessibility

- Keep one page-level `h1` in the brand panel; the form title remains a subordinate heading.
- Keep real labels associated with both inputs.
- Keep landmark structure: `aside` for product information and `main` for the sign-in form.
- Keep icons hidden from assistive technology when they are decorative.
- Preserve descriptive image `alt` text for the desktop logo and an empty `alt` for the decorative mobile duplicate.
- Ensure body text meets WCAG AA contrast and that color is not the only way to identify focus or errors.
- Respect the existing reduced-motion handling; any auth entrance treatment must be subtle and removable under `prefers-reduced-motion`.

## Implementation boundaries

Expected changes are limited to:

- `resources/views/auth/login.blade.php` for semantic markup, copy, classes, and the jQuery password toggle.
- `public/css/components.css` for the auth layout, form states, responsive behavior, and token usage.
- `public/css/tokens.css` for login-only dashboard-blue aliases and related auth values.

No route, controller, API, or password-reset file changes are planned.

## Verification

1. Run the relevant Laravel authentication tests.
2. Compile/cache the Blade views to catch template errors.
3. Check the login page in a browser at desktop and mobile widths.
4. Verify password show/hide behavior, validation alert rendering, keyboard focus, mobile wrapping, and absence of horizontal overflow.
5. Perform one visual review against the thesis: professional, calm, industrial, and easy to scan.

## Design decision

The refined field-operations split screen is preferred over a centered minimal page because it preserves CleanWay’s operational identity while removing the generic Bootstrap feel. The implementation stays intentionally small and avoids changing unrelated authenticated screens.
