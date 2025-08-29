<?php

namespace App\Services;

use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ComplianceService
{
    /**
     * Calculate compliance rate for a specific scope
     */
    public function calculateComplianceRate($departmentId = null, $programId = null, $userId = null)
    {
        $cacheKey = $this->generateCacheKey('compliance_rate', $departmentId, $programId, $userId);
        
        return Cache::remember($cacheKey, 300, function () use ($departmentId, $programId, $userId) {
            $query = ComplianceDocument::query();

            if ($userId) {
                $query->whereHas('assignment', function($q) use ($userId) {
                    $q->where('faculty_id', $userId);
                });
            } elseif ($programId) {
                $query->whereHas('assignment.user', function($q) use ($programId) {
                    $q->where('program_id', $programId);
                });
            } elseif ($departmentId) {
                $query->whereHas('assignment.user', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            $total = $query->count();
            $complied = $query->where('status', 'Complied')->count();

            return $total > 0 ? round(($complied / $total) * 100, 1) : 0;
        });
    }

    /**
     * Get compliance chart data optimized with single query
     */
    public function getComplianceChartData($departmentId = null, $programId = null)
    {
        $cacheKey = $this->generateCacheKey('compliance_chart', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 300, function () use ($departmentId, $programId) {
            $query = ComplianceDocument::query();

            if ($programId) {
                $query->whereHas('assignment.user', function($q) use ($programId) {
                    $q->where('program_id', $programId);
                });
            } elseif ($departmentId) {
                $query->whereHas('assignment.user', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            // Use single query with aggregation
            $results = $query->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Complied" THEN 1 ELSE 0 END) as complied'),
                DB::raw('SUM(CASE WHEN status = "Not Complied" THEN 1 ELSE 0 END) as pending')
            )->first();

            $total = $results->total ?? 0;
            $complied = $results->complied ?? 0;
            $pending = $results->pending ?? 0;

            return [
                'total' => $total,
                'complied' => $complied,
                'pending' => $pending,
                'percentage' => $total > 0 ? round(($complied / $total) * 100, 1) : 0,
            ];
        });
    }

    /**
     * Get faculty statistics with optimized queries
     */
    public function getFacultyStats($departmentId = null, $programId = null)
    {
        $cacheKey = $this->generateCacheKey('faculty_stats', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 600, function () use ($departmentId, $programId) {
            $query = FacultyAssignment::query();

            if ($programId) {
                $query->whereHas('user', function($q) use ($programId) {
                    $q->where('program_id', $programId);
                });
            } elseif ($departmentId) {
                $query->whereHas('user', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            $facultyCount = $query->distinct('faculty_id')->count('faculty_id');
            $assignmentCount = $query->count();

            return [
                'faculty_count' => $facultyCount,
                'assignment_count' => $assignmentCount,
            ];
        });
    }

    /**
     * Clear cache for a specific scope
     */
    public function clearCache($departmentId = null, $programId = null, $userId = null)
    {
        $patterns = [
            $this->generateCacheKey('compliance_rate', $departmentId, $programId, $userId),
            $this->generateCacheKey('compliance_chart', $departmentId, $programId),
            $this->generateCacheKey('faculty_stats', $departmentId, $programId),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey($type, $departmentId = null, $programId = null, $userId = null)
    {
        $parts = [$type];
        
        if ($userId) {
            $parts[] = "user_{$userId}";
        } elseif ($programId) {
            $parts[] = "program_{$programId}";
        } elseif ($departmentId) {
            $parts[] = "department_{$departmentId}";
        } else {
            $parts[] = 'global';
        }

        return implode('_', $parts);
    }
}
