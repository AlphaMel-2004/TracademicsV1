<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users (except MIS to avoid self-logging)
        if (Auth::check() && Auth::user()->role && Auth::user()->role->name !== 'MIS') {
            $this->logActivity($request);
        }

        return $response;
    }

    private function logActivity(Request $request)
    {
        try {
            $action = $this->determineAction($request);
            $description = $this->generateDescription($request, $action);

            UserActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'metadata' => [
                    'route' => $request->route()?->getName(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'parameters' => $request->all(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the application
            Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $route = $request->route()?->getName() ?? '';

        if (str_contains($route, 'login')) return 'login';
        if (str_contains($route, 'logout')) return 'logout';
        if ($method === 'POST' && str_contains($route, 'store')) return 'create';
        if ($method === 'PUT' || $method === 'PATCH') return 'update';
        if ($method === 'DELETE') return 'delete';
        if ($method === 'GET') return 'view';

        return 'action';
    }

    private function generateDescription(Request $request, string $action): string
    {
        $route = $request->route()?->getName() ?? $request->path();
        $user = Auth::user();

        return "{$user->name} ({$user->role->name}) performed {$action} on {$route}";
    }
}
