<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

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
        }

        return view('dashboard', compact('data', 'user'));
    }

    private function getVpaaData()
    {
        // Institution-wide rollup - using separate queries to avoid GROUP BY issues
        $departments = Department::all()->map(function ($department) {
            $department->faculty_count = User::where('department_id', $department->id)->count();
            $department->assignment_count = FacultyAssignment::whereHas('user', function($q) use ($department) {
                $q->where('department_id', $department->id);
            })->count();
            return $department;
        });

        $overallStats = [
            'total_faculty' => User::where('role_id', 4)->count(), // Faculty Member role
            'total_assignments' => FacultyAssignment::count(),
            'total_complied' => ComplianceDocument::where('status', 'Compiled')->count(),
            'total_required' => ComplianceDocument::count(),
        ];

        // Compliance chart data
        $complianceData = $this->getComplianceChartData();

        return [
            'type' => 'vpaa',
            'departments' => $departments,
            'overall_stats' => $overallStats,
            'compliance_chart' => $complianceData,
        ];
    }

    private function getDeanData($user)
    {
        // Department-level rollup - using separate queries to avoid GROUP BY issues
        $departmentId = $user->department_id;
        
        $programs = Program::where('department_id', $departmentId)->get()->map(function ($program) {
            $program->faculty_count = User::where('program_id', $program->id)->count();
            $program->assignment_count = FacultyAssignment::whereHas('user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->count();
            return $program;
        });

        $deptStats = [
            'faculty_count' => User::where('department_id', $departmentId)->where('role_id', 4)->count(),
            'assignment_count' => FacultyAssignment::whereHas('user', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->count(),
            'compliance_rate' => $this->calculateComplianceRate($departmentId),
        ];

        // Compliance chart data for department programs
        $complianceData = $this->getComplianceChartData($departmentId);

        return [
            'type' => 'dean',
            'programs' => $programs,
            'dept_stats' => $deptStats,
            'compliance_chart' => $complianceData,
        ];
    }

    private function getProgramHeadData($user)
    {
        // Program-level rollup
        $programId = $user->program_id;
        $faculty = User::where('program_id', $programId)
            ->where('role_id', 4)
            ->with(['facultyAssignments.complianceDocuments'])
            ->get();

        $progStats = [
            'faculty_count' => $faculty->count(),
            'assignment_count' => FacultyAssignment::where('program_id', $programId)->count(),
            'compliance_rate' => $this->calculateComplianceRate(null, $programId),
        ];

        // Compliance chart data for program faculty
        $complianceData = $this->getComplianceChartData(null, $programId);

        return [
            'type' => 'program_head',
            'faculty' => $faculty,
            'prog_stats' => $progStats,
            'compliance_chart' => $complianceData,
        ];
    }

    private function getFacultyData($user)
    {
        // Self-only view
        $assignments = FacultyAssignment::where('faculty_id', $user->id)
            ->with(['subject', 'term', 'complianceDocuments'])
            ->get();

        $complianceRate = $this->calculateComplianceRate(null, null, $user->id);

        // Compliance chart data for individual faculty
        $complianceData = $this->getComplianceChartData(null, null, $user->id);

        return [
            'type' => 'faculty',
            'assignments' => $assignments,
            'compliance_rate' => $complianceRate,
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
        $compiled = $query->where('status', 'Compiled')->count();
        $pending = $total - $compiled;

        return [
            'total' => $total,
            'compiled' => $compiled,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($compiled / $total) * 100, 1) : 0,
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
        $compiled = $query->where('status', 'Compiled')->count();

        return $total > 0 ? round(($compiled / $total) * 100, 1) : 0;
    }
}


