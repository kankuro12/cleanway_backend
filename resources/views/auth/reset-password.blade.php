<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New password — CleanWay Ops</title>
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
                    <h1 class="h4 mb-1">Choose a new password</h1>
                    <p class="eyebrow mb-4">Minimum 8 characters</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ $email }}" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New password</label>
                            <input type="password" id="password" name="password" class="form-control" required autofocus autocomplete="new-password">
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Reset password</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
