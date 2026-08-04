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

];
