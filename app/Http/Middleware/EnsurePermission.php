<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Require the authenticated user to hold at least one of the given
     * permission keys, e.g. "permission:1.1".
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()?->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
