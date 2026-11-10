<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->user()) {
            $route = $request->route();
            $entityType = null;
            $entityId = null;
            if ($route && $route->getAction()) {
                $action = $route->getAction();
                if (isset($action['as'])) {
                    $parts = explode('.', $action['as']);
                    $entityType = $parts[0] ?? null;
                }
            }
            $entityId = $route?->parameter('id') ?? $route?->parameter('{id}');
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->method(),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        return $response;
    }
}
