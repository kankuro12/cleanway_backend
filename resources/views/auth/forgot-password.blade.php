<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password — CleanWay Ops</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ versioned_asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ versioned_asset('css/components.css') }}" rel="stylesheet">
</head>
<body>
    <main class="auth-form-side" style="min-height: 100vh">
        <div class="auth-card">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 mb-1">Reset password</h1>
                    <p class="eyebrow mb-4">Enter email — we send a link</p>

                    @if (session('status'))
                        <div class="alert alert-success py-2" role="alert">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="email">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Send reset link</button>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="{{ route('login') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
