<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — CleanWay Ops</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ versioned_asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ versioned_asset('css/components.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-shell">
        {{-- Branded operations panel (lg+) --}}
        <aside class="auth-panel d-none d-lg-flex" aria-label="Product information">
            <div class="auth-brand">
                <span class="auth-brand-mark" aria-hidden="true">
                    <img src="{{ asset('logo.jpg') }}" alt="CleanWay logo">
                </span>
                <span class="auth-brand-text">
                    <span class="auth-brand-name">CleanWay</span>
                    <span class="auth-brand-tag">Field Operations</span>
                </span>
            </div>

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
        </aside>

        {{-- Sign-in form --}}
        <main class="auth-form-side">
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
        </main>
    </div>

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
</body>
</html>
