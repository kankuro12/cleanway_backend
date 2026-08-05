@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">System · Notifications</span>
            <h2 class="h3 mt-1 mb-0">Inbox</h2>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}" data-ajax>
            @csrf
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check2-all me-1" aria-hidden="true"></i>Mark all read
            </button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <ul class="nav nav-tabs mb-3 reveal" style="--d: 80ms" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-unread" type="button" role="tab">
                Unread <span class="badge text-bg-secondary ms-1" id="unread-count">{{ $notifications->total() }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-read" type="button" role="tab" id="read-tab-link">
                Read
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-unread" role="tabpanel">
            <div class="card shadow-sm reveal" style="--d: 100ms">
                <div class="list-group list-group-flush" id="unread-list">
                    @forelse ($notifications as $notification)
                        @include('partials.notification-item', ['notification' => $notification])
                    @empty
                        <div class="empty-state m-3">
                            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-check2-all"></i></span>
                            No unread notifications.
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="mt-3 reveal">{{ $notifications->links() }}</div>
        </div>

        <div class="tab-pane fade" id="tab-read" role="tabpanel">
            <div class="card shadow-sm reveal" style="--d: 100ms">
                <div class="list-group list-group-flush" id="read-list">
                    <div class="empty-state m-3">
                        <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
                        Loading…
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="read-more">
                    <i class="bi bi-arrow-down-circle me-1" aria-hidden="true"></i>Load more
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function ($) {
            var readLoaded = false;
            var readNextUrl = null;
            var readLoading = false;

            function flash(msg, ok) {
                $('#ajax-status').remove();
                $('.eyebrow').after('<div id="ajax-status" class="alert alert-' + (ok ? 'success' : 'danger') + ' py-2 reveal" role="alert">' + msg + '</div>');
            }

            function loadReadFeed(url) {
                if (readLoading) return;
                readLoading = true;
                axios.get(url)
                    .then(function (res) {
                        readLoading = false;
                        var $list = $('#read-list');
                        if ($list.find('.empty-state').length) $list.empty();
                        res.data.data.forEach(function (html) {
                            $list.append(html);
                        });
                        readNextUrl = res.data.next;
                        $('#read-more').toggleClass('d-none', !readNextUrl);
                    })
                    .catch(function () {
                        readLoading = false;
                        flash('Could not load read notifications.', false);
                    });
            }

            // Lazy load the Read tab only when it opens.
            $('#read-tab-link').on('shown.bs.tab', function () {
                if (readLoaded) return;
                readLoaded = true;
                loadReadFeed('{{ route('notifications.read-feed') }}');
            });

            $('#read-more').on('click', function () { loadReadFeed(readNextUrl); });

            // Mark one read → drop the row from the Unread tab.
            $(document).on('submit', 'form[data-ajax]', function (e) {
                e.preventDefault();
                var $form = $(this);
                axios.post($form.attr('action'), new FormData($form[0]))
                    .then(function (res) {
                        var $item = $form.closest('.list-group-item');
                        if ($item.length) {
                            $item.slideUp(160, function () { $(this).remove(); });
                            var $count = $('#unread-count');
                            $count.text(Math.max(0, (parseInt($count.text() || '0', 10) - 1)));
                            flash(res.data.count === undefined ? 'Notification marked as read.' : 'All notifications marked as read.', true);
                        } else {
                            $('#unread-list .list-group-item').remove();
                            $('#unread-count').text('0');
                            $('#unread-list').append(
                                '<div class="empty-state m-3"><span class="empty-state-icon" aria-hidden="true"><i class="bi bi-check2-all"></i></span>No unread notifications.</div>'
                            );
                            flash('All notifications marked as read.', true);
                        }
                    })
                    .catch(function (err) {
                        flash('Could not update notification: ' + (err.response?.data?.message || 'try again.'), false);
                    });
            });
        })(jQuery);
    </script>
@endpush
