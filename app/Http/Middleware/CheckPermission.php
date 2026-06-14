<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Usage in routes: middleware('permission:view_applications')
     */
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            return response()->json([
                'data'    => null,
                'message' => 'You do not have the required permission to perform this action.',
                'success' => false,
            ], 403);
        }

        return $next($request);
    }
}
