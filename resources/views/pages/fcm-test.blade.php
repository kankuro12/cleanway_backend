@extends('layouts.app')

@section('title', 'FCM Test')

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">Internal · FCM</span>
        <h1 class="h3 mt-1 mb-0">Push message test</h1>
        <p class="text-muted small mb-0">Ghost page — no sidebar link. Sends an in-app notification + FCM push to the chosen recipient(s).</p>
    </div>

    <div id="send-result" class="alert py-2 reveal" role="alert" style="display: none;"></div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('fcm-test.send') }}" class="reveal" style="--d: 80ms" id="fcm-test-form">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Recipient</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label d-block">Recipient type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient" value="user" id="r-user" checked>
                                <label class="form-check-label" for="r-user">Single user</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient" value="team" id="r-team">
                                <label class="form-check-label" for="r-team">Team</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="recipient" value="role" id="r-role">
                                <label class="form-check-label" for="r-role">Role group</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="pick-user">
                            <label for="user_id" class="form-label">User</label>
                            <select name="user_id" id="user_id" class="form-select">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} ({{ $user->email }}) · {{ $user->devices_count }} device(s) · {{ $user->status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="pick-team" style="display: none;">
                            <label for="team_id" class="form-label">Team</label>
                            <select name="team_id" id="team_id" class="form-select">
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }} ({{ $team->members_count }} member(s))</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="pick-role" style="display: none;">
                            <label for="role" class="form-label">Role group</label>
                            <select name="role" id="role" class="form-select">
                                @foreach ([0 => 'Admin', 1 => 'Supervisor', 2 => 'Cleaner'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') !== null && (int) old('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Message</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', 'FCM test message') }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="body" class="form-label">Body</label>
                        <textarea id="body" name="body" rows="3" class="form-control">{{ old('body', 'This is a test push from the operations console.') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" id="fcm-send-btn">
            <i class="bi bi-send me-1" aria-hidden="true"></i>Send test push
        </button>
    </form>
@endsection

@push('scripts')
    <script>
        (function ($) {
            $('input[name="recipient"]').on('change', function () {
                var v = $(this).val();
                $('#pick-user').toggle(v === 'user');
                $('#pick-team').toggle(v === 'team');
                $('#pick-role').toggle(v === 'role');
            });

            // AJAX send — no page reload.
            $('#fcm-test-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this), $btn = $('#fcm-send-btn'), $result = $('#send-result');

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Sending…');
                $result.hide().removeClass('alert-success alert-danger');

                axios.post($form.attr('action'), new FormData($form[0]))
                    .then(function (res) {
                        $result.addClass('alert-success').text(res.data.message || 'Sent.').show();
                    })
                    .catch(function (err) {
                        var msg = err.response?.data?.message
                            || Object.values(err.response?.data?.errors || {}).flat().join(' ')
                            || 'Send failed.';
                        $result.addClass('alert-danger').text(msg).show();
                    })
                    .finally(function () {
                        $btn.prop('disabled', false).html('<i class="bi bi-send me-1" aria-hidden="true"></i>Send test push');
                    });
            });
        })(jQuery);
    </script>
@endpush
