<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use App\Models\DocumentType;
use App\Models\Subject;

class DatabaseOptimizationService
{
    /**
     * Execute optimized bulk operations with transaction safety
     */
    public function executeBulkOperation(callable $operation, $maxRetries = 3)
    {
        return DB::transaction(function () use ($operation, $maxRetries) {
            $attempts = 0;
            
            while ($attempts < $maxRetries) {
                try {
                    return $operation();
                } catch (\Exception $e) {
                    $attempts++;
                    
                    if ($attempts >= $maxRetries) {
                        Log::error('Bulk operation failed after retries', [
                            'attempts' => $attempts,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                    
                    // Wait before retry
                    usleep(100000 * $attempts); // 100ms * attempt number
                }
            }
        });
    }

    /**
     * Optimized faculty compliance data with minimal queries
     */
    public function getFacultyComplianceData($filters = [])
    {
        $cacheKey = 'faculty_compliance_data_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $query = User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.faculty_type',
                'departments.name as department_name',
                'programs.name as program_name',
                'roles.name as role_name',
                DB::raw('COUNT(DISTINCT faculty_assignments.id) as total_assignments'),
                DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_documents'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Complied" THEN 1 ELSE 0 END) as pending_documents'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Applicable" THEN 1 ELSE 0 END) as na_documents'),
                DB::raw('ROUND((SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                         NULLIF(COUNT(DISTINCT compliance_documents.id), 0)) * 100, 2) as compliance_percentage'),
                DB::raw('MAX(compliance_documents.updated_at) as last_activity')
            ])
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('programs', 'users.program_id', '=', 'programs.id')
            ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
            ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
            ->where('roles.name', 'Faculty Member');

            // Apply filters
            if (isset($filters['department_id']) && $filters['department_id']) {
                $query->where('users.department_id', $filters['department_id']);
            }

            if (isset($filters['program_id']) && $filters['program_id']) {
                $query->where('users.program_id', $filters['program_id']);
            }

            if (isset($filters['faculty_type']) && $filters['faculty_type']) {
                $query->where('users.faculty_type', $filters['faculty_type']);
            }

            if (isset($filters['compliance_status']) && $filters['compliance_status']) {
                if ($filters['compliance_status'] === 'Complied') {
                    $query->havingRaw('compliance_percentage >= 80');
                } elseif ($filters['compliance_status'] === 'Partially Complied') {
                    $query->havingRaw('compliance_percentage BETWEEN 1 AND 79');
                } elseif ($filters['compliance_status'] === 'Not Complied') {
                    $query->havingRaw('compliance_percentage = 0 OR compliance_percentage IS NULL');
                }
            }

            return $query->groupBy([
                'users.id', 'users.name', 'users.email', 'users.faculty_type',
                'departments.name', 'programs.name', 'roles.name'
            ])
            ->orderBy('compliance_percentage', 'desc')
            ->orderBy('users.name')
            ->get();
        });
    }

    /**
     * Optimized subject performance analytics
     */
    public function getSubjectPerformanceAnalytics($filters = [])
    {
        $cacheKey = 'subject_performance_analytics_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 600, function () use ($filters) {
            $query = Subject::select([
                'subjects.code',
                'subjects.title',
                'subjects.description',
                'subjects.units',
                DB::raw('COUNT(DISTINCT faculty_assignments.id) as total_assignments'),
                DB::raw('COUNT(DISTINCT faculty_assignments.faculty_id) as unique_faculty'),
                DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_documents'),
                DB::raw('ROUND((SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                         NULLIF(COUNT(DISTINCT compliance_documents.id), 0)) * 100, 2) as compliance_rate'),
                DB::raw('AVG(CASE WHEN compliance_documents.status = "Complied" 
                         THEN TIMESTAMPDIFF(DAY, compliance_documents.created_at, compliance_documents.updated_at)
                         ELSE NULL END) as avg_completion_days'),
                DB::raw('COUNT(DISTINCT document_types.id) as required_document_types'),
                DB::raw('MAX(compliance_documents.updated_at) as last_submission')
            ])
            ->leftJoin('faculty_assignments', 'subjects.code', '=', 'faculty_assignments.subject_code')
            ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
            ->leftJoin('document_types', 'compliance_documents.document_type_id', '=', 'document_types.id')
            ->leftJoin('users', 'faculty_assignments.faculty_id', '=', 'users.id');

            // Apply filters
            if (isset($filters['department_id']) && $filters['department_id']) {
                $query->where('users.department_id', $filters['department_id']);
            }

            if (isset($filters['program_id']) && $filters['program_id']) {
                $query->where('users.program_id', $filters['program_id']);
            }

            if (isset($filters['semester_id']) && $filters['semester_id']) {
                $query->where('faculty_assignments.semester_id', $filters['semester_id']);
            }

            return $query->groupBy([
                'subjects.code', 'subjects.title', 'subjects.description', 'subjects.units'
            ])
            ->having('total_assignments', '>', 0)
            ->orderBy('compliance_rate', 'desc')
            ->orderBy('subjects.title')
            ->get();
        });
    }

    /**
     * Optimized document type effectiveness analysis
     */
    public function getDocumentTypeEffectiveness($filters = [])
    {
        $cacheKey = 'document_type_effectiveness_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 900, function () use ($filters) {
            $query = DocumentType::select([
                'document_types.id',
                'document_types.name',
                'document_types.description',
                'document_types.submission_type',
                DB::raw('COUNT(DISTINCT compliance_documents.id) as total_submissions'),
                DB::raw('COUNT(DISTINCT compliance_documents.assignment_id) as unique_assignments'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_submissions'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Complied" THEN 1 ELSE 0 END) as pending_submissions'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Applicable" THEN 1 ELSE 0 END) as na_submissions'),
                DB::raw('ROUND((SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                         NULLIF(COUNT(DISTINCT compliance_documents.id), 0)) * 100, 2) as completion_rate'),
                DB::raw('AVG(CASE WHEN compliance_documents.status = "Complied" 
                         THEN TIMESTAMPDIFF(HOUR, compliance_documents.created_at, compliance_documents.updated_at)
                         ELSE NULL END) as avg_completion_hours'),
                DB::raw('COUNT(DISTINCT CASE WHEN compliance_documents.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         THEN compliance_documents.id ELSE NULL END) as submissions_last_30_days'),
                DB::raw('MIN(compliance_documents.created_at) as first_submission'),
                DB::raw('MAX(compliance_documents.updated_at) as last_submission')
            ])
            ->leftJoin('compliance_documents', 'document_types.id', '=', 'compliance_documents.document_type_id')
            ->leftJoin('faculty_assignments', 'compliance_documents.assignment_id', '=', 'faculty_assignments.id')
            ->leftJoin('users', 'faculty_assignments.faculty_id', '=', 'users.id');

            // Apply filters
            if (isset($filters['department_id']) && $filters['department_id']) {
                $query->where('users.department_id', $filters['department_id']);
            }

            if (isset($filters['program_id']) && $filters['program_id']) {
                $query->where('users.program_id', $filters['program_id']);
            }

            if (isset($filters['submission_type']) && $filters['submission_type']) {
                $query->where('document_types.submission_type', $filters['submission_type']);
            }

            return $query->groupBy([
                'document_types.id', 'document_types.name', 'document_types.description', 'document_types.submission_type'
            ])
            ->orderBy('completion_rate', 'desc')
            ->orderBy('total_submissions', 'desc')
            ->get();
        });
    }

    /**
     * Get performance trending data for dashboard charts
     */
    public function getPerformanceTrending($days = 30, $filters = [])
    {
        $cacheKey = 'performance_trending_' . $days . '_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($days, $filters) {
            $query = DB::table('compliance_documents')
                ->select([
                    DB::raw('DATE(compliance_documents.updated_at) as date'),
                    DB::raw('COUNT(*) as total_activities'),
                    DB::raw('SUM(CASE WHEN status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
                    DB::raw('SUM(CASE WHEN status = "Not Complied" THEN 1 ELSE 0 END) as pending_count'),
                    DB::raw('COUNT(DISTINCT assignment_id) as active_assignments'),
                    DB::raw('COUNT(DISTINCT document_type_id) as active_document_types')
                ])
                ->join('faculty_assignments', 'compliance_documents.assignment_id', '=', 'faculty_assignments.id')
                ->join('users', 'faculty_assignments.faculty_id', '=', 'users.id')
                ->where('compliance_documents.updated_at', '>=', now()->subDays($days));

            // Apply filters
            if (isset($filters['department_id']) && $filters['department_id']) {
                $query->where('users.department_id', $filters['department_id']);
            }

            if (isset($filters['program_id']) && $filters['program_id']) {
                $query->where('users.program_id', $filters['program_id']);
            }

            return $query->groupBy(DB::raw('DATE(compliance_documents.updated_at)'))
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    $item->compliance_rate = $item->total_activities > 0 
                        ? round(($item->complied_count / $item->total_activities) * 100, 2) 
                        : 0;
                    return $item;
                });
        });
    }

    /**
     * Execute database maintenance and optimization
     */
    public function performDatabaseMaintenance()
    {
        try {
            Log::info('Starting database maintenance');

            // Clear expired cache entries
            Cache::flush();

            // Analyze table performance (MySQL specific)
            $tables = [
                'users', 'faculty_assignments', 'compliance_documents', 
                'document_types', 'subjects', 'departments', 'programs'
            ];

            foreach ($tables as $table) {
                try {
                    DB::statement("ANALYZE TABLE {$table}");
                } catch (\Exception $e) {
                    Log::warning("Failed to analyze table {$table}: " . $e->getMessage());
                }
            }

            // Clear old activity logs (older than 90 days)
            DB::table('user_activity_logs')
                ->where('created_at', '<', now()->subDays(90))
                ->delete();

            Log::info('Database maintenance completed successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Database maintenance failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database performance metrics
     */
    public function getDatabasePerformanceMetrics()
    {
        try {
            $metrics = [];

            // Table sizes and row counts
            $tableStats = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    ROUND((index_length / 1024 / 1024), 2) AS index_size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                AND table_name IN ('users', 'faculty_assignments', 'compliance_documents', 'document_types')
                ORDER BY (data_length + index_length) DESC
            ");

            $metrics['table_stats'] = $tableStats;

            // Query performance indicators
            $metrics['cache_hit_rate'] = $this->calculateCacheHitRate();
            $metrics['average_query_time'] = $this->getAverageQueryTime();
            
            return $metrics;
        } catch (\Exception $e) {
            Log::error('Failed to get database performance metrics: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate cache hit rate approximation
     */
    private function calculateCacheHitRate()
    {
        try {
            // Simple approximation based on cache store performance
            $testKeys = ['test_key_1', 'test_key_2', 'test_key_3'];
            $hits = 0;

            foreach ($testKeys as $key) {
                Cache::put($key, 'test_value', 60);
                if (Cache::get($key) === 'test_value') {
                    $hits++;
                }
                Cache::forget($key);
            }

            return ($hits / count($testKeys)) * 100;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get average query time estimation
     */
    private function getAverageQueryTime()
    {
        try {
            $start = microtime(true);
            
            // Execute a simple query to estimate performance
            DB::select('SELECT COUNT(*) FROM users LIMIT 1');
            
            $end = microtime(true);
            
            return round(($end - $start) * 1000, 2); // Convert to milliseconds
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clear all optimization caches
     */
    public function clearOptimizationCaches()
    {
        $patterns = [
            'faculty_compliance_data_*',
            'subject_performance_analytics_*',
            'document_type_effectiveness_*',
            'performance_trending_*'
        ];

        foreach ($patterns as $pattern) {
            // Since Laravel doesn't support wildcard cache clearing,
            // we'll flush all cache as a safe approach
            Cache::flush();
            break;
        }

        Log::info('Optimization caches cleared');
    }
}
