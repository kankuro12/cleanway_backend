<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Require the authenticated user to hold at least one of the given
     * roles (0 = admin, 1 = supervisor, 2 = cleaner), e.g. "role:0,1".
     */
    public function handle(Request $request, Closure $next, int ...$roles): Response
    {
        if (! $request->user() || ! $request->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
