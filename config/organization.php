<?php

return [

    /*
    | Organization-wide operational settings. Cached aggressively; the
    | settings admin UI (reports phase) will manage these at runtime.
    */
    'name' => env('ORGANIZATION_NAME', 'CleanWay'),

    /*
    | Fallback level 3 of the effective check-in radius chain (spec §2.3):
    | property → category → here → system fallback in EffectiveRadiusResolver.
    */
    'default_check_in_radius_meters' => env('DEFAULT_CHECK_IN_RADIUS_METERS', 150),

];
