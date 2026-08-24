# CleanWay Login Design Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the generic-looking CleanWay login presentation with a calm, professional field-operations split screen while preserving the existing authentication flow.

**Architecture:** Keep the standalone Blade login page and its existing semantic two-column structure. Add login-scoped design tokens, express the visual system through focused `auth-*` CSS selectors, and use the existing jQuery CDN convention for the password toggle. No controller, route, session, API, or password-reset changes are required.

**Tech Stack:** Laravel 13 Blade, Bootstrap 5.3 CDN, jQuery 3.7 CDN, CSS custom properties, Archivo, IBM Plex Mono.

## Global Constraints

- Auth pages are standalone (no `layouts.app`) — use `auth-*` classes, split-screen on `lg`+ (navy brand panel + form side).
- Color values must use `--cw-*` tokens; use dispatch navy for the panel and the dashboard’s existing blue palette for the primary action.
- Use Archivo for display text and IBM Plex Mono for mono uppercase labels.
- Keep real labels, one page-level `h1`, visible blue focus outlines, WCAG AA body-text contrast, landmarks, image `alt` text, and reflow down to 320px.
- Keep the existing login route, CSRF protection, validation, remember-me behavior, forgot-password link, and password visibility behavior.
- Limit the visual palette change to login-scoped tokens; do not change the global blue primary tokens used elsewhere.
- Use existing CDN conventions only; add no package, npm dependency, or component library.
- Use `php84` for PHP CLI commands in this workspace.
- The worktree already contains uncommitted edits to `resources/views/auth/login.blade.php` and `public/css/components.css`; preserve the intent of those edits while improving the result.
- Do not create a git commit unless the user explicitly requests one.

---

## File map

- Modify `public/css/tokens.css` to hold login-scoped dashboard-blue aliases, panel grid/glow values, focus ring, and auth input sizing.
- Modify the auth section of `public/css/components.css` to remove the generic glow/card treatment and define the responsive field-operations composition.
- Modify `resources/views/auth/login.blade.php` to apply the auth body class, use the approved copy and form surface markup, expose validation state, and replace vanilla password-toggle code with jQuery.
- Do not modify routes, controllers, tests, API documentation, or the password-reset views.

### Task 1: Add login-scoped design tokens

**Files:**
- Modify: `public/css/tokens.css` near the existing semantic color tokens after `--cw-accent-deep`.

**Interfaces:**
- Consumes: Existing `--cw-ink-900`, `--cw-gray-*`, `--cw-canvas`, `--cw-surface`, `--cw-border`, and spacing/radius tokens.
- Produces: `--cw-auth-accent`, `--cw-auth-accent-hover`, `--cw-auth-accent-active`, `--cw-auth-accent-ink`, `--cw-auth-accent-tint`, `--cw-auth-accent-deep`, `--cw-auth-panel-grid`, `--cw-auth-panel-glow`, `--cw-auth-focus-ring`, `--cw-auth-mark-border`, `--cw-auth-on-dark`, and `--cw-auth-input-height` for the auth CSS block.

- [ ] **Step 1: Add the scoped auth tokens without changing global primary values**

Add this block inside `:root`:

```css
  /* Auth-only field-operations accent — follows the dashboard blue palette */
  --cw-auth-accent: var(--cw-primary);
  --cw-auth-accent-hover: var(--cw-primary-hover);
  --cw-auth-accent-active: var(--cw-primary-active);
  --cw-auth-accent-ink: var(--cw-accent-ink);
  --cw-auth-accent-tint: var(--cw-accent-tint);
  --cw-auth-accent-deep: var(--cw-accent-deep);
  --cw-auth-panel-grid: rgba(255,255,255,0.055);
  --cw-auth-panel-glow: rgba(2,132,199,0.11);
  --cw-auth-focus-ring: rgba(2,132,199,0.32);
  --cw-auth-mark-border: rgba(255,255,255,0.18);
  --cw-auth-on-dark: #FFFFFF;
  --cw-auth-input-height: 52px;
```

- [ ] **Step 2: Check that global tokens remain unchanged**

Run:

```powershell
git diff -- public/css/tokens.css
```

Expected: the diff contains only the new auth token block; `--cw-primary`, `--cw-accent`, and their existing values remain unchanged.

### Task 2: Rebuild the auth CSS composition

**Files:**
- Modify: `public/css/components.css` in the auth section beginning at the `/* Auth` comment.

**Interfaces:**
- Consumes: The auth token variables from Task 1 and existing global design tokens.
- Produces: Stable `.auth-body`, `.auth-shell`, `.auth-panel`, `.auth-form-side`, `.auth-form-panel`, `.auth-input`, `.auth-input-toggle`, `.auth-submit`, `.auth-alert`, and responsive auth states used by the login view.

- [ ] **Step 1: Replace the existing auth block with the restrained split-screen styles**

Use the following structure and values. Keep the surrounding non-auth CSS unchanged:

```css
/* Auth — refined field-operations sign-in */
.auth-body{
  min-width:320px;
  background:var(--cw-canvas);
  color:var(--cw-text);
}
.auth-shell{
  min-height:100svh;
  display:flex;
  background:var(--cw-canvas);
}
.auth-panel{
  flex:0 1 48%;
  min-height:100svh;
  position:relative;
  overflow:hidden;
  display:flex;
  flex-direction:column;
  padding:clamp(var(--cw-space-xl), 5vw, 64px);
  background-color:var(--cw-ink-900);
  background-image:
    radial-gradient(ellipse at 15% 0%, var(--cw-auth-panel-glow), transparent 60%),
    linear-gradient(var(--cw-auth-panel-grid) 1px, transparent 1px),
    linear-gradient(90deg, var(--cw-auth-panel-grid) 1px, transparent 1px);
  background-size:auto, 32px 32px, 32px 32px;
  color:var(--cw-gray-300);
}
.auth-panel::after{
  content:'';
  position:absolute;
  left:0;
  right:0;
  bottom:0;
  height:4px;
  background:var(--cw-auth-accent);
}
.auth-panel > *{
  position:relative;
  z-index:1;
}
.auth-brand{
  display:flex;
  align-items:center;
  gap:var(--cw-space-sm);
}
.auth-brand-mark{
  width:44px;
  height:44px;
  flex:0 0 44px;
  overflow:hidden;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border:1px solid var(--cw-auth-mark-border);
  border-radius:var(--cw-radius-sm);
  background:var(--cw-auth-on-dark);
}
.auth-brand-mark img{
  width:100%;
  height:100%;
  object-fit:cover;
}
.auth-brand-text{
  display:flex;
  flex-direction:column;
  line-height:1;
}
.auth-brand-name{
  color:var(--cw-auth-on-dark);
  font-family:var(--cw-font-display);
  font-size:1.35rem;
  font-weight:900;
  letter-spacing:-0.02em;
}
.auth-brand-tag{
  margin-top:5px;
  color:var(--cw-gray-400);
  font-family:var(--cw-font-mono);
  font-size:0.6875rem;
  font-weight:600;
  letter-spacing:0.14em;
  text-transform:uppercase;
}
.auth-panel-sub{
  margin-top:clamp(64px, 10vh, 112px);
  color:var(--cw-auth-accent);
  font-family:var(--cw-font-mono);
  font-size:0.75rem;
  font-weight:600;
  letter-spacing:0.12em;
  text-transform:uppercase;
}
.auth-panel-title{
  max-width:12ch;
  margin:var(--cw-space-sm) 0 0;
  color:var(--cw-auth-on-dark);
  font-family:var(--cw-font-display);
  font-size:clamp(2.25rem, 3.5vw, 3.25rem);
  font-weight:900;
  line-height:0.99;
  letter-spacing:-0.03em;
}
.auth-panel-title .accent{
  color:var(--cw-auth-accent);
}
.auth-panel-points{
  display:flex;
  flex-direction:column;
  gap:var(--cw-space-md);
  margin:var(--cw-space-xl) 0 0;
  padding:0;
  list-style:none;
}
.auth-panel-points li{
  display:flex;
  align-items:center;
  gap:var(--cw-space-sm);
  color:var(--cw-gray-300);
  font-size:0.9375rem;
}
.auth-panel-points i{
  flex:0 0 20px;
  color:var(--cw-auth-accent);
  font-size:1rem;
  text-align:center;
}
.auth-form-side{
  flex:1 1 52%;
  min-width:0;
  min-height:100svh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:clamp(var(--cw-space-lg), 5vw, 72px);
  background:var(--cw-canvas);
}
.auth-card{
  width:100%;
  max-width:430px;
}
.auth-form-panel{
  position:relative;
  overflow:hidden;
  padding:40px;
  border:1px solid var(--cw-border);
  border-radius:var(--cw-radius-md);
  background:var(--cw-surface);
  box-shadow:var(--cw-shadow-md);
}
.auth-form-panel::before{
  content:'';
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:4px;
  background:var(--cw-auth-accent);
}
.auth-card-head{
  margin-bottom:var(--cw-space-lg);
}
.auth-card-title{
  margin:var(--cw-space-sm) 0 var(--cw-space-sm);
  color:var(--cw-text-strong);
  font-family:var(--cw-font-display);
  font-size:clamp(1.75rem, 3vw, 2rem);
  font-weight:900;
  letter-spacing:-0.025em;
}
.auth-card-intro{
  margin:0;
  color:var(--cw-muted);
  font-size:0.9375rem;
  line-height:1.6;
}
.auth-form-panel .form-label{
  color:var(--cw-text-strong);
  margin-bottom:var(--cw-space-xs);
}
.auth-input{
  position:relative;
}
.auth-input .form-control{
  min-height:var(--cw-auth-input-height);
  padding-left:44px;
  padding-right:48px;
  border-radius:var(--cw-radius-sm);
}
.auth-input .form-control.is-invalid{
  border-color:var(--cw-danger);
  box-shadow:0 0 0 3px var(--cw-danger-tint);
}
.auth-input > i{
  position:absolute;
  top:50%;
  left:15px;
  z-index:1;
  transform:translateY(-50%);
  color:var(--cw-muted);
  font-size:1rem;
  pointer-events:none;
}
.auth-input-toggle{
  position:absolute;
  top:50%;
  right:4px;
  min-width:44px;
  min-height:44px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  transform:translateY(-50%);
  border:0;
  border-radius:var(--cw-radius-sm);
  background:transparent;
  color:var(--cw-muted);
}
.auth-input-toggle:hover{
  background:var(--cw-surface-2);
  color:var(--cw-text-strong);
}
.auth-input-toggle:focus-visible{
  outline:2px solid var(--cw-auth-accent);
  outline-offset:2px;
}
.auth-form-meta{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:var(--cw-space-sm);
  margin-bottom:var(--cw-space-lg);
}
.auth-form-meta .form-check{
  margin:0;
}
.auth-form-meta .form-check-input:checked{
  border-color:var(--cw-auth-accent);
  background-color:var(--cw-auth-accent);
}
.auth-form-meta a{
  color:var(--cw-auth-accent-deep);
  font-weight:600;
}
.auth-form-meta a:hover{
  color:var(--cw-auth-accent-active);
}
.auth-submit{
  min-height:var(--cw-auth-input-height);
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:var(--cw-space-sm);
  border-color:var(--cw-auth-accent);
  background:var(--cw-auth-accent);
  color:var(--cw-auth-accent-ink);
  transition:background-color var(--cw-dur-fast) var(--cw-ease), border-color var(--cw-dur-fast) var(--cw-ease), color var(--cw-dur-fast) var(--cw-ease);
}
.auth-submit:hover{
  border-color:var(--cw-auth-accent-hover);
  background:var(--cw-auth-accent-hover);
  color:var(--cw-auth-accent-ink);
}
.auth-submit:active{
  border-color:var(--cw-auth-accent-active);
  background:var(--cw-auth-accent-active);
  color:var(--cw-auth-accent-ink);
}
.auth-alert{
  border-left-width:3px;
}
.auth-footer{
  margin:var(--cw-space-lg) 0 0;
  text-align:center;
}
.auth-shell a:focus-visible,
.auth-shell button:focus-visible,
.auth-shell .form-control:focus-visible{
  outline:2px solid var(--cw-auth-accent);
  outline-offset:2px;
}
.auth-form-panel .form-control:focus{
  border-color:var(--cw-auth-accent);
  box-shadow:0 0 0 3px var(--cw-auth-focus-ring);
}
@media (max-width:575.98px){
  .auth-form-side{
    align-items:flex-start;
    padding:var(--cw-space-md);
  }
  .auth-form-panel{
    padding:32px var(--cw-space-lg);
    box-shadow:var(--cw-shadow-sm);
  }
  .auth-panel-title{
    font-size:2rem;
  }
}
@media (max-width:359.98px){
  .auth-form-meta{
    align-items:flex-start;
    flex-direction:column;
  }
}
@media (prefers-reduced-motion:reduce){
  .auth-input-toggle,
  .auth-submit,
  .form-control{
    transition:none;
  }
}
```

- [ ] **Step 2: Keep color and spacing values tokenized**

Review the replaced block and remove any new hardcoded color or spacing values not present in the specified block. `1px`, `2px`, `3px`, `4px`, and breakpoint values are structural CSS values; all palette values must come from the token variables.

- [ ] **Step 3: Check the stylesheet for syntax errors**

Run:

```powershell
git diff --check
```

Expected: no whitespace or patch errors.

### Task 3: Update the login view and interaction script

**Files:**
- Modify: `resources/views/auth/login.blade.php`.

**Interfaces:**
- Consumes: The selectors from Task 2 and named existing routes.
- Produces: Approved copy, a single `.auth-form-panel`, accessible labels/states, and jQuery-driven `[data-toggle-password]` behavior.

- [ ] **Step 1: Apply the auth body class**

Change the opening body element to:

```blade
<body class="auth-body">
```

- [ ] **Step 2: Update the panel copy without changing its semantic structure**

Keep the existing desktop `aside`, logo asset, one `h1`, and three-item list. Change the visible text to:

```blade
<div class="auth-panel-sub">CleanWay Ops · field operations</div>

<h1 class="auth-panel-title">
    Clean crews.<br>
    Verified sites.<br>
    <span class="accent">On schedule.</span>
</h1>

<ul class="auth-panel-points">
    <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Location-aware attendance</span></li>
    <li><i class="bi bi-camera" aria-hidden="true"></i><span>Photo evidence on every task</span></li>
    <li><i class="bi bi-shield-check" aria-hidden="true"></i><span>Audited approvals end to end</span></li>
</ul>
```

- [ ] **Step 3: Replace the generic Bootstrap card with the auth form panel**

Inside the existing `main.auth-form-side`, retain the mobile brand lockup and form controls but replace the `card border-0 shadow-sm` wrapper with:

```blade
<div class="auth-card">
    <section class="auth-form-panel" aria-labelledby="login-title">
        <div class="auth-brand d-lg-none mb-4">
            <span class="auth-brand-mark" aria-hidden="true">
                <img src="{{ asset('logo.jpg') }}" alt="">
            </span>
            <span class="auth-brand-text">
                <span class="auth-brand-name">CleanWay</span>
                <span class="auth-brand-tag">Field Ops</span>
            </span>
        </div>

        <div class="auth-card-head">
            <span class="eyebrow">Authorized personnel</span>
            <h2 id="login-title" class="auth-card-title">Sign in to CleanWay</h2>
            <p class="auth-card-intro">Use your work account to continue.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger auth-alert py-2" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="auth-input">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@company.co.nz" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="auth-input">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="auth-input-toggle" data-toggle-password aria-label="Show password">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="auth-form-meta">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label small">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn w-100 auth-submit">
                <span>Sign in</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
    </section>

    <p class="auth-footer eyebrow">CleanWay Ops · internal use only</p>
</div>
```

Use this structure as the final error/form markup, with the following requirements:

- Add `auth-alert` and `aria-live="assertive"` to the existing error alert.
- Add `@error('email') is-invalid @enderror` and `@error('password') is-invalid @enderror` to the corresponding inputs so validation has a visible field state.
- Keep the current labels, `autocomplete`, `autofocus`, `required`, CSRF directive, remember checkbox, and forgot-password route.
- Add `class="auth-form-meta"` to the remember-me/forgot-password flex row.
- Keep the primary button text `Sign in`, the arrow icon, and `auth-submit`.

- [ ] **Step 4: Load the existing jQuery CDN and replace vanilla toggle code**

Place the existing jQuery CDN script before the inline password-toggle script:

```blade
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function ($) {
        $('[data-toggle-password]').on('click', function () {
            var $button = $(this);
            var $input = $button.closest('.auth-input').find('input');
            var show = $input.attr('type') === 'password';

            $input.attr('type', show ? 'text' : 'password');
            $button.attr('aria-label', show ? 'Hide password' : 'Show password');
            $button.find('i')
                .toggleClass('bi-eye', !show)
                .toggleClass('bi-eye-slash', show);
        });
    })(jQuery);
</script>
```

Do not add Axios because the login page has no AJAX behavior. Do not use `document.querySelector`, `addEventListener`, or `fetch` in the login view.

- [ ] **Step 5: Confirm the view has no unrelated route or behavior changes**

Run:

```powershell
git diff -- resources/views/auth/login.blade.php
```

Expected: the diff changes only auth markup, approved copy, classes, validation presentation, and the password-toggle implementation.

### Task 4: Validate behavior and visual output

**Files:**
- Test: `tests/Feature/AuthTest.php`
- Test: `tests/Feature/PasswordResetTest.php`
- Verify: `resources/views/auth/login.blade.php` in a browser at desktop and mobile widths.

**Interfaces:**
- Consumes: Completed token, CSS, and Blade changes from Tasks 1–3.
- Produces: Evidence that existing authentication behavior remains intact and the new layout is usable.

- [ ] **Step 1: Compile Blade views**

Run:

```powershell
php84 artisan view:cache
```

Expected: the command completes successfully without a Blade compilation error.

- [ ] **Step 2: Run the focused authentication tests**

Run:

```powershell
php84 artisan test tests/Feature/AuthTest.php tests/Feature/PasswordResetTest.php
```

Expected: all tests in both files pass.

- [ ] **Step 3: Run PHP formatting on any dirty PHP files**

Run:

```powershell
php84 vendor/bin/pint --dirty
```

Expected: Pint completes successfully; CSS and Blade files are not reformatted by Pint.

- [ ] **Step 4: Start the local Laravel server only if no existing server is running**

Check the IDE terminal state first. If no Laravel server is active, run:

```powershell
php84 artisan serve
```

Expected: Laravel serves the application on its reported local URL.

- [ ] **Step 5: Browser-check the login at desktop and mobile sizes**

At the login URL, verify all of the following:

1. At a desktop width, the navy brand panel and light form side fill the viewport without a horizontal scrollbar.
2. The brand panel shows the compact logo, one headline, three aligned points, low-contrast grid, and thin blue rule.
3. The form reads as one focused surface with the title, support line, labels, controls, remember-me row, and blue CTA in a clear order.
4. At a mobile width of 320px or wider, the panel is hidden, the mobile brand lockup is visible, the form remains readable, and the remember/link row wraps without overflow.
5. Clicking the password icon changes the input between password/text and updates the accessible label and icon.
6. Keyboard focus is visibly blue on links, the password toggle, and inputs.
7. Submitting invalid credentials still shows the server-rendered error alert and preserves the existing route behavior.

- [ ] **Step 6: Check the final diff**

Run:

```powershell
git diff --check
git status --short
```

Expected: no patch errors; only the approved login design files plus the design/plan documents are changed, with unrelated pre-existing files left untouched.
