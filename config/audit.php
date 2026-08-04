<?php

return [

    /*
    | Master switch. Disable to stop writing audit entries (not recommended).
    */
    'enabled' => env('AUDIT_ENABLED', true),

    /*
    | Queue the write in production (requires a queue worker). Tests run sync.
    */
    'queue' => env('AUDIT_QUEUE', false),

];
