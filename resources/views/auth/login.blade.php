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
    <link href="{{ asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-panel d-none d-lg-flex" aria-label="Product information">
            <div class="auth-panel-brand">
                <span class="sidebar-brand-mark p-0 overflow-hidden rounded-2 d-inline-flex justify-content-center align-items-center" style="width:32px; height:32px;" aria-hidden="true">
                    <img src="{{ asset('logo.jpg') }}" alt="CleanWay Logo" style="width:100%; height:100%; object-fit:cover;">
                </span>
                <div>
                    <span class="sidebar-brand-name d-block">CLEANWAY</span>
                    <span class="sidebar-brand-tag">Field Operations</span>
                </div>
            </div>

            <div class="auth-panel-sub mt-5">Internal workforce system</div>

            <h1 class="auth-panel-title">
                Clean crews.<br>
                Verified sites.<br>
                <span class="accent">On the clock.</span>
            </h1>

            <ul class="auth-panel-points">
                <li><i class="bi bi-geo-alt" aria-hidden="true"></i>GPS-verified check-in &amp; check-out</li>
                <li><i class="bi bi-camera" aria-hidden="true"></i>Photo evidence on every task</li>
                <li><i class="bi bi-shield-check" aria-hidden="true"></i>Audited approvals end to end</li>
            </ul>

            <div class="sidebar-hazard" aria-hidden="true"></div>
        </aside>

        <main class="auth-form-side">
            <div class="auth-card">
                <div class="card">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 mb-1">Sign in</h2>
                        <p class="eyebrow mb-4">Authorized personnel only</p>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2" role="alert">
                                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>{{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="email">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="mb-4 form-check">
                                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                <label for="remember" class="form-check-label small">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Sign in
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
                        </div>
                    </div>
                </div>
                <p class="eyebrow text-center mt-4 mb-0">CleanWay Ops · internal use only</p>
            </div>
        </main>
    </div>
</body>
</html>
