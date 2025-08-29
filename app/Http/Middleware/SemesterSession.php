<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Semester;
use App\Models\SemesterSession as SemesterSessionModel;

class SemesterSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for MIS users and login/logout routes
        if (!Auth::check() || 
            (Auth::user()->role && Auth::user()->role->name === 'MIS') ||
            $request->routeIs('login') || 
            $request->routeIs('logout')) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Check if user has a current semester session
        if (!$user->current_semester_id) {
            // Redirect to semester selection if no current semester
            return redirect()->route('semester.select');
        }

        // Check if the current semester is still active
        $currentSemester = Semester::find($user->current_semester_id);
        if (!$currentSemester || !$currentSemester->is_active) {
            // Clear the current semester and redirect to selection
            $user->current_semester_id = null;
            $user->save();
            return redirect()->route('semester.select');
        }

        // Update or create semester session
        $this->updateSemesterSession($user, $currentSemester);

        return $next($request);
    }

    private function updateSemesterSession($user, $semester)
    {
        try {
            $activeSession = SemesterSessionModel::where('user_id', $user->id)
                ->where('semester_id', $semester->id)
                ->where('is_active', true)
                ->first();

            if (!$activeSession) {
                // Create new session
                SemesterSessionModel::create([
                    'user_id' => $user->id,
                    'semester_id' => $semester->id,
                    'logged_in_at' => now(),
                    'is_active' => true,
                ]);
            } else {
                // Update existing session timestamp
                $activeSession->touch();
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::error('Semester session update failed: ' . $e->getMessage());
        }
    }
}
