@php
    $web = config('firebase.web');
    $ready = $web['api_key'] && $web['project_id'] && $web['messaging_sender_id'] && $web['app_id'];
    $jsConfig = [
        'apiKey' => $web['api_key'],
        'authDomain' => $web['auth_domain'],
        'projectId' => $web['project_id'],
        'storageBucket' => $web['storage_bucket'],
        'messagingSenderId' => $web['messaging_sender_id'],
        'appId' => $web['app_id'],
        'measurementId' => $web['measurement_id'],
    ];
@endphp
@if($ready)
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.17.0/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.17.0/firebase-messaging.js";

        const firebaseConfig = @json($jsConfig);

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        // FCM message while the UI is open → simple toast, top-right.
        onMessage(messaging, function (payload) {
            var title = escapeHtml(payload.notification?.title || payload.data?.title || 'CleanWay Ops');
            var body = escapeHtml(payload.notification?.body || payload.data?.body || '');

            var $container = $('#fcm-toasts');
            if (!$container.length) {
                $container = $('<div id="fcm-toasts" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;"></div>').appendTo('body');
            }

            var $toast = $(
                '<div class="toast align-items-center border-0 shadow-lg bg-dark text-white" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex align-items-center p-2">' +
                '<img src="{{ asset('logo.jpg') }}" alt="CleanWay" class="rounded me-2 border border-secondary" style="width:36px; height:36px; object-fit:cover;">' +
                '<div class="toast-body flex-grow-1 min-w-0 py-1">' +
                '<strong class="d-block text-truncate text-warning">' + title + '</strong>' +
                '<span class="small text-white-50 d-block text-truncate">' + body + '</span>' +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div></div>'
            );

            $container.append($toast);
            $toast.on('hidden.bs.toast', function () { $(this).remove(); });

            new bootstrap.Toast($toast[0], { delay: 5000 }).show();
        });

        function registerDevice() {
            if (!('serviceWorker' in navigator) || !('Notification' in window)) return;
            if (Notification.permission === 'denied') return;

            Notification.requestPermission().then(function (permission) {
                if (permission !== 'granted') return;
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then(function () {
                        if (!@json($web['vapid_key'] ?: null)) {
                            console.warn('FIREBASE_VAPID_KEY not set — skipping web push token.');
                            return;
                        }
                        return getToken(messaging, {
                            vapidKey: @json($web['vapid_key']),
                            serviceWorkerRegistration: null
                        });
                    })
                    .then(function (token) {
                        if (!token) return;
                        axios.post('{{ route('devices.store') }}', {
                            fcm_token: token,
                            platform: 'web'
                        }).catch(function () { /* retried on next page load */ });
                    })
                    .catch(function (err) { console.warn('Firebase registration:', err); });
            });
        }

        if (document.readyState === 'complete') registerDevice();
        else window.addEventListener('load', registerDevice);
    </script>
@endif
