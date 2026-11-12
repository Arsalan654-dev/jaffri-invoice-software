<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('erp.login');
        }
        foreach ($permissions as $permission) {
            if (! $user->hasPermission($permission)) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Permission denied'], 403);
                }

                abort(403, 'You cannot allowed by admin to access this');
            }
        }

        return $next($request);
    }
}
