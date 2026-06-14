<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Usage in routes: middleware('role:officer,superadmin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            return response()->json([
                'data'    => null,
                'message' => 'You do not have permission to access this resource.',
                'success' => false,
            ], 403);
        }

        return $next($request);
    }
}
