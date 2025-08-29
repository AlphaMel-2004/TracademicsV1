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
            return redirect()->route('login')->with('error', 'User role not assigned. Please contact administrator.');
        }

        // Redirect MIS users to their own dashboard
        if ($user->role->name === 'MIS') {
            return redirect()->route('mis.dashboard');
        }

        switch ($user->role->name) {
            case 'VPAA':
                $data = $this->getVpaaData();
                break;
            case 'Dean':
                $data = $this->getDeanData($user);
                break;
            case 'Program Head':
                $data = $this->getProgramHeadData($user);
                break;
            case 'Faculty Member':
                $data = $this->getFacultyData($user);
                break;
            default:
                return redirect()->route('login')->with('error', 'Invalid user role.');
        }

        return view('dashboard', compact('data', 'user'));
    }

    private function getVpaaData()
    {
        // Institution-wide rollup - departments in ascending order with part-time/full-time breakdown
        // Optimize with single query using joins and aggregations
        $departments = Department::orderBy('name', 'asc')
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', 'Faculty Member')
            ->select('departments.*')
            ->selectRaw('COUNT(users.id) as total_faculty')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "part-time" THEN 1 ELSE 0 END) as part_time')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "full-time" THEN 1 ELSE 0 END) as full_time')
            ->groupBy('departments.id', 'departments.name', 'departments.created_at', 'departments.updated_at')
            ->get();

        // Optimize overall stats with single queries using scopes
        $overallStats = [
            'total_faculty' => User::facultyMembers()->count(),
            'total_assignments' => FacultyAssignment::count(),
            'total_complied' => ComplianceDocument::complied()->count(),
            'total_required' => ComplianceDocument::count(),
        ];

        // Compliance chart data with filters
        $complianceData = $this->complianceService->getComplianceChartData();

        return [
            'type' => 'vpaa',
            'departments' => $departments,
            'overall_stats' => $overallStats,
            'compliance_chart' => $complianceData,
        ];
    }

    private function getDeanData($user)
    {
        // Department-level rollup with optimized queries
        $departmentId = $user->department_id;
        
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


