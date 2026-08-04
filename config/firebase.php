<?php

return [

    /*
    | Firebase project credentials (FCM HTTP v1).
    |
    | FIREBASE_CREDENTIALS points to a service-account JSON file downloaded
    | from the Firebase console (Project settings → Service accounts).
    | Set FIREBASE_PROJECT_ID to the project id from the same console.
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    'credentials' => env('FIREBASE_CREDENTIALS'),

    /*
    | Whether FCM pushes are enabled at all. Push delivery is skipped
    | (delivery row marked skipped) when disabled or unconfigured.
    */
    'enabled' => env('FIREBASE_ENABLED', false),

    /*
    | Where to send the user when the notification is tapped.
    */
    'web_action_url' => env('FIREBASE_WEB_ACTION_URL', '/admin/notifications'),

    /*
    | Firebase web app config — public client values embedded in the page
    | and the messaging service worker. Not secrets (any web client ships
    | them), but kept in env so each environment points at its own project.
    */
    'web' => [
        'api_key' => env('FIREBASE_WEB_API_KEY'),
        'auth_domain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_WEB_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_WEB_APP_ID'),
        'measurement_id' => env('FIREBASE_WEB_MEASUREMENT_ID'),
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
    ],

];
