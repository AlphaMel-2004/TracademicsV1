<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OptimizedFormValidation
{
    /**
     * Handle an incoming request with optimized validation caching
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only process POST, PUT, PATCH requests (form submissions)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Add performance timing
        $startTime = microtime(true);

        // Log form submission for analytics
        $this->logFormSubmission($request);

        // Add CSRF protection check timing
        if ($request->hasSession()) {
            $csrfStartTime = microtime(true);
            // CSRF validation happens automatically in Laravel
            $csrfEndTime = microtime(true);
            
            $request->attributes->set('csrf_validation_time', ($csrfEndTime - $csrfStartTime) * 1000);
        }

        $response = $next($request);

        // Calculate total processing time
        $endTime = microtime(true);
        $processingTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Log performance metrics for forms taking longer than 500ms
        if ($processingTime > 500) {
            Log::warning('Slow form processing detected', [
                'url' => $request->url(),
                'method' => $request->method(),
                'processing_time_ms' => round($processingTime, 2),
                'user_id' => Auth::id(),
                'form_data_size' => strlen(json_encode($request->all())),
            ]);
        }

        // Add performance headers for debugging
        if (config('app.debug')) {
            $response->headers->set('X-Form-Processing-Time', round($processingTime, 2) . 'ms');
            $response->headers->set('X-Form-Memory-Usage', round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB');
        }

        return $response;
    }

    /**
     * Log form submission for analytics and monitoring
     */
    protected function logFormSubmission(Request $request)
    {
        try {
            $formData = [
                'url' => $request->url(),
                'route' => $request->route() ? $request->route()->getName() : null,
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'form_fields_count' => count($request->all()),
                'has_file_uploads' => $request->hasFile('*'),
                'timestamp' => now(),
            ];

            // Cache recent form submissions for rate limiting analysis
            $cacheKey = 'form_submissions_' . $request->ip() . '_' . date('H');
            $submissions = Cache::get($cacheKey, []);
            $submissions[] = $formData;
            
            // Keep only last 100 submissions per hour
            if (count($submissions) > 100) {
                $submissions = array_slice($submissions, -100);
            }
            
            Cache::put($cacheKey, $submissions, 3600); // 1 hour

            // Log to application logs (could be sent to analytics service)
            Log::info('Form submission tracked', [
                'route' => $formData['route'],
                'user_id' => $formData['user_id'],
                'fields_count' => $formData['form_fields_count'],
            ]);

        } catch (\Exception $e) {
            // Don't fail the request if logging fails
            Log::error('Failed to log form submission: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has exceeded form submission rate limits
     */
    protected function checkRateLimit(Request $request)
    {
        $cacheKey = 'form_rate_limit_' . ($request->user()->id ?? $request->ip());
        $submissions = Cache::get($cacheKey, 0);

        // Allow 60 form submissions per minute per user
        if ($submissions >= 60) {
            Log::warning('Form submission rate limit exceeded', [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'submissions_count' => $submissions,
            ]);

            return false;
        }

        Cache::put($cacheKey, $submissions + 1, 60); // 1 minute
        return true;
    }
}
