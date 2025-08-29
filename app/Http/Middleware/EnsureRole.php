<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\UserActivityLog;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        if (!$user) {
            Log::warning('Unauthorized access attempt - No authenticated user', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->view('auth.unauthorized', [
                'message' => 'Authentication required. Please log in to access this resource.'
            ], 403);
        }
        
        // Support multiple roles separated by comma
        $allowedRoles = array_map('trim', explode(',', $role));
        $userRole = optional($user->role)->name ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_roles' => $allowedRoles,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);

            // Log to user activity
            UserActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "Attempted to access {$request->path()} without proper role. User role: {$userRole}, Required: " . implode(', ', $allowedRoles),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->view('auth.unauthorized', [
                'message' => "Access denied. This resource requires one of the following roles: " . implode(', ', $allowedRoles) . ". Your current role: {$userRole}"
            ], 403);
        }
        
        return $next($request);
    }
}



