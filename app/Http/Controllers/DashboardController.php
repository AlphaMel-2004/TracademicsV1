<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use App\Models\Department;
use App\Models\Program;
use App\Services\ComplianceService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }
    public function index()
    {
        $user = Auth::user();
        $data = [];

        // Check if user has a role
        if (!$user->role) {
            \Log::error('User without role accessing dashboard', ['user_id' => $user->id]);
            return redirect()->route('login')->with('error', 'User role not assigned. Please contact administrator.');
        }

        // Redirect MIS users to their own dashboard
        if ($user->role->name === 'MIS') {
            return redirect()->route('mis.dashboard');
        }

        try {
            switch ($user->role->name) {
                case 'VPAA':
                    $data = $this->getVpaaDataOptimized();
                    break;
                case 'Dean':
                    $data = $this->getDeanDataOptimized($user);
                    break;
                case 'Program Head':
                    $data = $this->getProgramHeadDataOptimized($user);
                    break;
                case 'Faculty Member':
                    $data = $this->getFacultyDataOptimized($user);
                    break;
                default:
                    \Log::warning('User with invalid role accessing dashboard', [
                        'user_id' => $user->id, 
                        'role' => $user->role->name
                    ]);
                    return redirect()->route('login')->with('error', 'Invalid user role.');
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard data retrieval failed', [
                'user_id' => $user->id,
                'role' => $user->role->name,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to basic data structure
            $data = [
                'type' => strtolower(str_replace(' ', '_', $user->role->name)),
                'error' => true,
                'message' => 'Some dashboard data may be temporarily unavailable.'
            ];
        }

        return view('dashboard', compact('data', 'user'));
    }

    /**
     * Optimized VPAA data retrieval with advanced caching and query optimization
     */
    private function getVpaaDataOptimized()
    {
        // Use ComplianceService for optimized performance metrics
        $performanceMetrics = $this->complianceService->getPerformanceMetrics();
        
        // Single optimized query for department stats
        $departments = Department::select([
            'departments.id',
            'departments.name',
            DB::raw('COUNT(DISTINCT users.id) as total_faculty'),
            DB::raw('COUNT(DISTINCT CASE WHEN users.faculty_type = "Full-time" THEN users.id END) as full_time'),
            DB::raw('COUNT(DISTINCT CASE WHEN users.faculty_type = "Part-time" THEN users.id END) as part_time'),
            DB::raw('COUNT(DISTINCT faculty_assignments.id) as total_assignments'),
            DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
        ])
        ->leftJoin('users', function($join) {
            $join->on('departments.id', '=', 'users.department_id')
                 ->join('roles', 'users.role_id', '=', 'roles.id')
                 ->where('roles.name', 'Faculty Member');
        })
        ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
        ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
        ->groupBy('departments.id', 'departments.name')
        ->orderBy('departments.name')
        ->get()
        ->map(function ($dept) {
            $dept->compliance_rate = $dept->total_documents > 0 
                ? round(($dept->complied_count / $dept->total_documents) * 100, 1) 
                : 0;
            return $dept;
        });

        // Get compliance chart data and faculty summary
        $complianceData = $this->complianceService->getComplianceChartData();
        $facultyStats = $this->complianceService->getFacultyStats();
        $topFaculty = $this->complianceService->getFacultyComplianceSummary(null, null, 10);

        return [
            'type' => 'vpaa',
            'departments' => $departments,
            'performance_metrics' => $performanceMetrics,
            'compliance_chart' => $complianceData,
            'faculty_stats' => $facultyStats,
            'top_faculty' => $topFaculty,
            'overall_stats' => [
                'total_faculty' => $departments->sum('total_faculty'),
                'total_assignments' => $departments->sum('total_assignments'),
                'total_documents' => $departments->sum('total_documents'),
                'total_complied' => $departments->sum('complied_count'),
                'overall_compliance_rate' => $performanceMetrics['compliance_rate'] ?? 0,
            ],
        ];
    }

    /**
     * Optimized Dean data retrieval with department-specific metrics
     */
    private function getDeanDataOptimized($user)
    {
        $departmentId = $user->department_id;
        
        if (!$departmentId) {
            \Log::warning('Dean user without department', ['user_id' => $user->id]);
            return ['type' => 'dean', 'error' => true, 'message' => 'No department assigned.'];
        }

        // Get performance metrics for the department
        $performanceMetrics = $this->complianceService->getPerformanceMetrics($departmentId);
        
        // Single optimized query for program stats within department
        $programs = Program::select([
            'programs.id',
            'programs.name',
            'programs.description',
            DB::raw('COUNT(DISTINCT users.id) as total_faculty'),
            DB::raw('COUNT(DISTINCT faculty_assignments.id) as total_assignments'),
            DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
        ])
        ->where('programs.department_id', $departmentId)
        ->leftJoin('users', function($join) {
            $join->on('programs.id', '=', 'users.program_id')
                 ->join('roles', 'users.role_id', '=', 'roles.id')
                 ->where('roles.name', 'Faculty Member');
        })
        ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
        ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
        ->groupBy('programs.id', 'programs.name', 'programs.description')
        ->orderBy('programs.name')
        ->get()
        ->map(function ($program) {
            $program->compliance_rate = $program->total_documents > 0 
                ? round(($program->complied_count / $program->total_documents) * 100, 1) 
                : 0;
            return $program;
        });

        // Get department-specific data
        $complianceData = $this->complianceService->getComplianceChartData($departmentId);
        $facultyStats = $this->complianceService->getFacultyStats($departmentId);
        $facultySummary = $this->complianceService->getFacultyComplianceSummary($departmentId, null, 15);
        $subjectOverview = $this->complianceService->getSubjectComplianceOverview($departmentId);

        return [
            'type' => 'dean',
            'department_id' => $departmentId,
            'programs' => $programs,
            'performance_metrics' => $performanceMetrics,
            'compliance_chart' => $complianceData,
            'faculty_stats' => $facultyStats,
            'faculty_summary' => $facultySummary,
            'subject_overview' => $subjectOverview,
            'department_stats' => [
                'total_programs' => $programs->count(),
                'total_faculty' => $programs->sum('total_faculty'),
                'total_assignments' => $programs->sum('total_assignments'),
                'total_documents' => $programs->sum('total_documents'),
                'total_complied' => $programs->sum('complied_count'),
                'department_compliance_rate' => $performanceMetrics['compliance_rate'] ?? 0,
            ],
        ];
    }

    /**
     * Optimized Program Head data retrieval with program-specific focus
     */
    private function getProgramHeadDataOptimized($user)
    {
        $programId = $user->program_id;
        
        if (!$programId) {
            \Log::warning('Program Head user without program', ['user_id' => $user->id]);
            return [
                'type' => 'program_head', 
                'error' => true, 
                'message' => 'No program assigned.',
                'prog_stats' => [
                    'faculty_count' => 0,
                    'total_faculty' => 0,
                    'full_time_faculty' => 0,
                    'part_time_faculty' => 0,
                    'total_assignments' => 0,
                    'assignment_count' => 0,
                    'total_documents' => 0,
                    'total_complied' => 0,
                    'total_pending' => 0,
                    'compliance_rate' => 0,
                ],
                'faculty' => collect(),
                'compliance_chart' => [
                    'total' => 0,
                    'complied' => 0,
                    'pending' => 0,
                    'percentage' => 0,
                ],
                'faculty_stats' => [
                    'total_faculty' => 0,
                    'active_faculty' => 0,
                    'compliance_rate' => 0,
                ],
                'subject_overview' => [],
                'document_type_stats' => [],
                'performance_metrics' => [
                    'compliance_rate' => 0,
                    'total_documents' => 0,
                    'complied_documents' => 0,
                ],
            ];
        }

        // Get performance metrics for the program
        $performanceMetrics = $this->complianceService->getPerformanceMetrics(null, $programId);
        
        // Single optimized query for faculty under this program
        $faculty = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.faculty_type',
            DB::raw('COUNT(DISTINCT faculty_assignments.id) as assignment_count'),
            DB::raw('COUNT(DISTINCT compliance_documents.id) as document_count'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
            DB::raw('COUNT(DISTINCT CASE WHEN compliance_documents.status = "Not Complied" THEN compliance_documents.id END) as pending_count'),
        ])
        ->where('users.program_id', $programId)
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->where('roles.name', 'Faculty Member')
        ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
        ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
        ->groupBy('users.id', 'users.name', 'users.email', 'users.faculty_type')
        ->orderBy('users.name')
        ->get()
        ->map(function ($member) {
            $member->compliance_rate = $member->document_count > 0 
                ? round(($member->complied_count / $member->document_count) * 100, 1) 
                : 0;
            return $member;
        });

        // Get program-specific data
        $complianceData = $this->complianceService->getComplianceChartData(null, $programId);
        $facultyStats = $this->complianceService->getFacultyStats(null, $programId);
        $subjectOverview = $this->complianceService->getSubjectComplianceOverview(null, $programId);
        $documentTypeStats = $this->complianceService->getDocumentTypeStats(null, $programId);

        return [
            'type' => 'program_head',
            'program_id' => $programId,
            'faculty' => $faculty,
            'performance_metrics' => $performanceMetrics,
            'compliance_chart' => $complianceData,
            'faculty_stats' => $facultyStats,
            'subject_overview' => $subjectOverview,
            'document_type_stats' => $documentTypeStats,
            'prog_stats' => [
                'total_faculty' => $faculty->count(),
                'full_time_faculty' => $faculty->where('faculty_type', 'Full-time')->count(),
                'part_time_faculty' => $faculty->where('faculty_type', 'Part-time')->count(),
                'total_assignments' => $faculty->sum('assignment_count'),
                'total_documents' => $faculty->sum('document_count'),
                'total_complied' => $faculty->sum('complied_count'),
                'total_pending' => $faculty->sum('pending_count'),
                'compliance_rate' => $performanceMetrics['compliance_rate'] ?? 0,
            ],
        ];
    }

    /**
     * Optimized Faculty Member data retrieval with personal dashboard focus
     */
    private function getFacultyDataOptimized($user)
    {
        // Get performance metrics for this specific faculty member
        $performanceMetrics = $this->complianceService->getPerformanceMetrics(null, null);
        $personalMetrics = $this->complianceService->calculateComplianceRate(null, null, $user->id);
        
        // Single optimized query for faculty assignments and compliance status
        $assignments = FacultyAssignment::select([
            'faculty_assignments.id',
            'faculty_assignments.subject_code',
            'faculty_assignments.subject_description',
            'semesters.name as semester_name',
            'semesters.year as semester_year',
            DB::raw('COUNT(DISTINCT compliance_documents.id) as total_documents'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Complied" THEN 1 ELSE 0 END) as complied_count'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Complied" THEN 1 ELSE 0 END) as pending_count'),
            DB::raw('SUM(CASE WHEN compliance_documents.status = "Not Applicable" THEN 1 ELSE 0 END) as na_count'),
        ])
        ->where('faculty_assignments.faculty_id', $user->id)
        ->join('semesters', 'faculty_assignments.semester_id', '=', 'semesters.id')
        ->leftJoin('compliance_documents', 'faculty_assignments.id', '=', 'compliance_documents.assignment_id')
        ->groupBy([
            'faculty_assignments.id',
            'faculty_assignments.subject_code',
            'faculty_assignments.subject_description',
            'semesters.name',
            'semesters.year'
        ])
        ->orderBy('semesters.year', 'desc')
        ->orderBy('faculty_assignments.subject_code')
        ->get()
        ->map(function ($assignment) {
            $assignment->compliance_rate = $assignment->total_documents > 0 
                ? round(($assignment->complied_count / $assignment->total_documents) * 100, 1) 
                : 0;
            // Add a subject_title for compatibility
            $assignment->subject_title = $assignment->subject_description;
            return $assignment;
        });

        // Get recent compliance activities
        $recentActivities = ComplianceDocument::select([
            'compliance_documents.id',
            'compliance_documents.status',
            'compliance_documents.updated_at',
            'document_types.name as document_name',
            'faculty_assignments.subject_description as subject_title',
            'faculty_assignments.subject_code'
        ])
        ->join('faculty_assignments', 'compliance_documents.assignment_id', '=', 'faculty_assignments.id')
        ->join('document_types', 'compliance_documents.document_type_id', '=', 'document_types.id')
        ->where('faculty_assignments.faculty_id', $user->id)
        ->orderBy('compliance_documents.updated_at', 'desc')
        ->limit(10)
        ->get();

        return [
            'type' => 'faculty',
            'user_id' => $user->id,
            'assignments' => $assignments,
            'recent_activities' => $recentActivities,
            'personal_compliance_rate' => $personalMetrics,
            'performance_metrics' => $performanceMetrics,
            'personal_stats' => [
                'total_assignments' => $assignments->count(),
                'total_documents' => $assignments->sum('total_documents'),
                'total_complied' => $assignments->sum('complied_count'),
                'total_pending' => $assignments->sum('pending_count'),
                'total_na' => $assignments->sum('na_count'),
                'personal_compliance_rate' => $personalMetrics,
                'subjects_assigned' => $assignments->pluck('subject_code')->unique()->count(),
            ],
        ];
    }

    // Keep the original methods for backward compatibility (deprecated)
    private function getVpaaData()
    {
        \Log::warning('Using deprecated getVpaaData method');
        return $this->getVpaaDataOptimized();
        
        // Optimize with single query using joins and aggregations
        $programs = Program::where('programs.department_id', $departmentId)
            ->leftJoin('users', function($join) {
                $join->on('programs.id', '=', 'users.program_id');
            })
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
            ->where('roles.name', 'Faculty Member')
            ->select('programs.*')
            ->selectRaw('COUNT(DISTINCT users.id) as total_faculty')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "part-time" THEN 1 ELSE 0 END) as part_time')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "full-time" THEN 1 ELSE 0 END) as full_time')
            ->selectRaw('COUNT(DISTINCT faculty_assignments.id) as assignment_count')
            ->groupBy('programs.id', 'programs.name', 'programs.department_id', 'programs.created_at', 'programs.updated_at')
            ->get();

        // Optimize department faculty count
        $departmentFacultyCount = User::facultyMembers()->byDepartment($departmentId)->count();

        $totalAssignments = FacultyAssignment::whereHas('user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        })->count();

        $deptStats = [
            'faculty_count' => $departmentFacultyCount,
            'assignment_count' => $totalAssignments,
            'compliance_rate' => $this->complianceService->calculateComplianceRate($departmentId),
        ];

        // Get the department's programs for filters
        $departmentPrograms = Program::where('department_id', $departmentId)->get(['id', 'name']);
        
        // Compliance chart with program filter
        $complianceChartData = $this->complianceService->getComplianceChartData($departmentId);

        return [
            'type' => 'dean',
            'programs' => $programs,
            'dept_stats' => $deptStats,
            'compliance_chart' => $complianceChartData,
            'department_programs' => $departmentPrograms, // Add for filter dropdown
        ];
    }

    private function getProgramHeadData($user)
    {
        // Program-level rollup with optimized queries using scopes
        $programId = $user->program_id;
        $faculty = User::facultyMembers()->byProgram($programId)
            ->with(['facultyAssignments'])
            ->get()
            ->map(function($facultyMember) {
                $facultyMember->subjects_assigned = $facultyMember->facultyAssignments->count();
                return $facultyMember;
            });

        $totalSubjectsAssigned = FacultyAssignment::whereHas('user', function($q) use ($programId) {
            $q->where('program_id', $programId);
        })->count();

        $progStats = [
            'faculty_count' => $faculty->count(),
            'assignment_count' => $totalSubjectsAssigned,
            'compliance_rate' => $this->complianceService->calculateComplianceRate(null, $programId),
        ];

        // Compliance chart data for program faculty
        $complianceData = $this->complianceService->getComplianceChartData(null, $programId);

        return [
            'type' => 'program_head',
            'faculty' => $faculty,
            'prog_stats' => $progStats,
            'compliance_chart' => $complianceData,
        ];
    }

    private function getFacultyData($user)
    {
        // Self-only view with updated naming
        $assignments = FacultyAssignment::where('faculty_id', $user->id)
            ->with(['subject', 'complianceDocuments'])
            ->get();

        $facultyStats = [
            'assignment_count' => $assignments->count(),
            'complied_count' => ComplianceDocument::whereHas('assignment', function($q) use ($user) {
                $q->where('faculty_id', $user->id);
            })->where('status', 'Complied')->count(),
            'total_required' => ComplianceDocument::whereHas('assignment', function($q) use ($user) {
                $q->where('faculty_id', $user->id);
            })->count(),
        ];

        // Calculate compliance rate
        $facultyStats['compliance_rate'] = $facultyStats['total_required'] > 0 
            ? round(($facultyStats['complied_count'] / $facultyStats['total_required']) * 100, 1) 
            : 0;

        // Compliance chart data for individual faculty
        $complianceData = $this->getComplianceChartData(null, null, $user->id);

        return [
            'type' => 'faculty',
            'assignments' => $assignments,
            'compliance_rate' => $facultyStats['compliance_rate'],
            'compliance_chart' => $complianceData,
        ];
    }

    private function getComplianceChartData($departmentId = null, $programId = null, $userId = null)
    {
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
        $pending = $total - $complied;

        return [
            'total' => $total,
            'complied' => $complied,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($complied / $total) * 100, 1) : 0,
        ];
    }

    private function calculateComplianceRate($departmentId = null, $programId = null, $userId = null)
    {
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
    }

    private function getDepartmentComplianceChartData($departmentId)
    {
        // Get compliance data filtered by department with program breakdown
        $programs = Program::where('department_id', $departmentId)->get();
        $chartData = [];

        foreach ($programs as $program) {
            $totalDocs = ComplianceDocument::whereHas('assignment.user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->count();

            $compliedDocs = ComplianceDocument::whereHas('assignment.user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->where('status', 'Complied')->count();

            $chartData[] = [
                'program' => $program->name,
                'complied' => $compliedDocs,
                'total' => $totalDocs,
                'rate' => $totalDocs > 0 ? round(($compliedDocs / $totalDocs) * 100, 1) : 0
            ];
        }

        return $chartData;
    }

    // API method for getting programs by department
    public function getDepartmentPrograms($departmentId)
    {
        $programs = Program::where('department_id', $departmentId)->get(['id', 'name']);
        return response()->json($programs);
    }

    // API method for getting filtered compliance data
    public function getComplianceData(Request $request)
    {
        $departmentId = $request->get('department_id');
        $programId = $request->get('program_id');
        
        // Use the ComplianceService for getting the chart data with better caching
        $complianceData = $this->complianceService->getComplianceChartData($departmentId, $programId);
        
        return response()->json($complianceData);
    }
}


