<?php

return [

    /*
    | GPS / geofence validation (spec §12).
    */
    'geofence_enforced' => env('GPS_GEOFENCE_ENFORCED', env('APP_ENV') === 'testing'),
    'max_accuracy_meters' => env('GPS_MAX_ACCURACY_METERS', 50),

    /*
    | Out-of-radius behavior: accept | exception | override | reject.
    | - accept:   event recorded as inside=0, no exception row
    | - exception: event recorded, gps_exceptions row created, check-in allowed
    | - override:  event recorded, exception row created, check-in BLOCKED until a
    |              manager resolves the exception with approval
    | - reject:    event NOT recorded, check-in blocked outright
    */
    'out_of_radius_policy' => env('GPS_OUT_OF_RADIUS_POLICY', 'exception'),

    'missing_coordinates_policy' => env('GPS_MISSING_COORDINATES_POLICY', 'override'),

    /*
    | Mandatory GPS check-out when enabled.
    */
    'require_gps_checkout' => env('GPS_REQUIRE_CHECKOUT', false),

    /*
    | Standalone Office Geofence Defaults.
    */
    'office_latitude' => env('OFFICE_LATITUDE', 27.7172),
    'office_longitude' => env('OFFICE_LONGITUDE', 85.3240),
    'office_radius_meters' => env('OFFICE_RADIUS_METERS', 100),

    /*
    | Completion gate (spec §13.2).
    */
    'require_completion_remarks' => env('TASK_REQUIRE_COMPLETION_REMARKS', true),
    'require_incident_acknowledgement' => env('TASK_REQUIRE_INCIDENT_ACK', false),

];
