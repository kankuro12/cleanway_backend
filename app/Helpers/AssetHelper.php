<?php

if (! function_exists('versioned_asset')) {
    /**
     * Generate versioned asset URL for cache-busting.
     * ponytail: query-string append only; use Vite manifest if build hashing needed.
     */
    function versioned_asset(string $path): string
    {
        $version = config('app.asset_version');

        $url = asset($path);

        if ($version === null || $version === '') {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . $version;
    }
}
