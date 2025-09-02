<?php

namespace App\Services;

use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

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
            $this->generateCacheKey('faculty_compliance_summary', $departmentId, $programId),
            $this->generateCacheKey('subject_compliance_overview', $departmentId, $programId),
            $this->generateCacheKey('document_type_stats', $departmentId, $programId),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get faculty compliance summary with optimized batch queries
     */
    public function getFacultyComplianceSummary($departmentId = null, $programId = null, $limit = 20)
    {
        $cacheKey = $this->generateCacheKey('faculty_compliance_summary', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 300, function () use ($departmentId, $programId, $limit) {
            // Use single optimized query with joins to avoid N+1 queries
            $query = User::select([
                'users.id',
                'users.name',
                'users.email',
                'departments.name as department_name',
                'programs.name as program_name',
                DB::raw('COUNT(DISTINCT faculty_assignments.id) as total_assignments'),
                DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
                DB::raw('ROUND(
                    (SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(DISTINCT compliance_documents.id), 0)) * 100, 1
                ) as compliance_percentage')
            ])
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('programs', 'users.program_id', '=', 'programs.id')
            ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
            ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
            ->where('users.role_id', function($subQuery) {
                $subQuery->select('id')->from('roles')->where('name', 'Faculty Member');
            });

            if ($programId) {
                $query->where('users.program_id', $programId);
            } elseif ($departmentId) {
                $query->where('users.department_id', $departmentId);
            }

            return $query->groupBy([
                'users.id', 
                'users.name', 
                'users.email', 
                'departments.name', 
                'programs.name'
            ])
            ->orderByDesc('compliance_percentage')
            ->limit($limit)
            ->get();
        });
    }

    /**
     * Get subject compliance overview with batch processing
     */
    public function getSubjectComplianceOverview($departmentId = null, $programId = null)
    {
        $cacheKey = $this->generateCacheKey('subject_compliance_overview', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 400, function () use ($departmentId, $programId) {
            $query = FacultyAssignment::select([
                'faculty_assignments.subject_code as code',
                'faculty_assignments.subject_description as title',
                'faculty_assignments.subject_description as description',
                DB::raw('COUNT(DISTINCT faculty_assignments.id) as assignment_count'),
                DB::raw('COUNT(DISTINCT compliance_documents.id) as document_count'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
                DB::raw('ROUND(
                    (SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(DISTINCT compliance_documents.id), 0)) * 100, 1
                ) as compliance_rate')
            ])
            ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
            ->join('users', 'faculty_assignments.faculty_id', '=', 'users.id');

            if ($programId) {
                $query->where('users.program_id', $programId);
            } elseif ($departmentId) {
                $query->where('users.department_id', $departmentId);
            }

            return $query->groupBy([
                'faculty_assignments.subject_code',
                'faculty_assignments.subject_description'
            ])
            ->orderByDesc('compliance_rate')
            ->get();
        });
    }

    /**
     * Get document type statistics for performance analysis
     */
    public function getDocumentTypeStats($departmentId = null, $programId = null)
    {
        $cacheKey = $this->generateCacheKey('document_type_stats', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 600, function () use ($departmentId, $programId) {
            $query = DocumentType::select([
                'document_types.id',
                'document_types.name',
                'document_types.description',
                'document_types.submission_type',
                DB::raw('COUNT(compliance_documents.id) as total_submissions'),
                DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_submissions'),
                DB::raw('ROUND(
                    (SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) / 
                     NULLIF(COUNT(compliance_documents.id), 0)) * 100, 1
                ) as compliance_rate'),
                DB::raw('AVG(CASE 
                    WHEN compliance_documents.status = "Complied" 
                    THEN TIMESTAMPDIFF(HOUR, compliance_documents.created_at, compliance_documents.updated_at)
                    ELSE NULL 
                END) as avg_completion_hours')
            ])
            ->leftJoin('compliance_documents', 'document_types.id', '=', 'compliance_documents.document_type_id')
            ->leftJoin('faculty_assignments', 'compliance_documents.assignment_id', '=', 'faculty_assignments.id')
            ->leftJoin('users', 'faculty_assignments.faculty_id', '=', 'users.id');

            if ($programId) {
                $query->where('users.program_id', $programId);
            } elseif ($departmentId) {
                $query->where('users.department_id', $departmentId);
            }

            return $query->groupBy([
                'document_types.id',
                'document_types.name',
                'document_types.description',
                'document_types.submission_type'
            ])
            ->orderByDesc('compliance_rate')
            ->get();
        });
    }

    /**
     * Bulk update compliance status with transaction safety
     */
    public function bulkUpdateComplianceStatus(array $complianceIds, string $status)
    {
        return DB::transaction(function () use ($complianceIds, $status) {
            $affected = ComplianceDocument::whereIn('id', $complianceIds)
                ->update([
                    'status' => $status,
                    'updated_at' => now()
                ]);

            // Clear relevant cache after bulk operation
            $this->clearAllCache();

            return $affected;
        });
    }

    /**
     * Get performance metrics for dashboard optimization
     */
    public function getPerformanceMetrics($departmentId = null, $programId = null)
    {
        $cacheKey = $this->generateCacheKey('performance_metrics', $departmentId, $programId);
        
        return Cache::remember($cacheKey, 900, function () use ($departmentId, $programId) {
            // Single query to get multiple metrics
            $baseQuery = ComplianceDocument::query();
            
            if ($programId) {
                $baseQuery->whereHas('assignment.user', function($q) use ($programId) {
                    $q->where('program_id', $programId);
                });
            } elseif ($departmentId) {
                $baseQuery->whereHas('assignment.user', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            $metrics = $baseQuery->select([
                DB::raw('COUNT(*) as total_documents'),
                DB::raw('COUNT(DISTINCT assignment_id) as unique_assignments'),
                DB::raw('SUM(CASE WHEN status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
                DB::raw('SUM(CASE WHEN status = "Not Complied" THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN status = "Not Applicable" THEN 1 ELSE 0 END) as na_count'),
                DB::raw('AVG(CASE 
                    WHEN status = "Complied" AND updated_at > created_at
                    THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) 
                    ELSE NULL 
                END) as avg_completion_time_hours'),
                DB::raw('COUNT(CASE 
                    WHEN status = "Complied" AND DATE(updated_at) = CURDATE() 
                    THEN 1 
                    ELSE NULL 
                END) as completed_today'),
                DB::raw('COUNT(CASE 
                    WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                    THEN 1 
                    ELSE NULL 
                END) as created_this_week')
            ])->first();

            return [
                'total_documents' => $metrics->total_documents ?? 0,
                'unique_assignments' => $metrics->unique_assignments ?? 0,
                'complied_count' => $metrics->complied_count ?? 0,
                'pending_count' => $metrics->pending_count ?? 0,
                'na_count' => $metrics->na_count ?? 0,
                'avg_completion_time_hours' => round($metrics->avg_completion_time_hours ?? 0, 2),
                'completed_today' => $metrics->completed_today ?? 0,
                'created_this_week' => $metrics->created_this_week ?? 0,
                'compliance_rate' => $metrics->total_documents > 0 
                    ? round(($metrics->complied_count / $metrics->total_documents) * 100, 2) 
                    : 0,
            ];
        });
    }

    /**
     * Clear all cache patterns
     */
    public function clearAllCache()
    {
        $patterns = [
            'compliance_rate_*',
            'compliance_chart_*',
            'faculty_stats_*',
            'faculty_compliance_summary_*',
            'subject_compliance_overview_*',
            'document_type_stats_*',
            'performance_metrics_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::flush(); // For simplicity, clear all cache
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
