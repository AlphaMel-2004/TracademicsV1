<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        
        // Support multiple roles separated by comma
        $allowedRoles = array_map('trim', explode(',', $role));
        $userRole = optional($user->role)->name ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Unauthorized.');
        }
        
        return $next($request);
    }
}



