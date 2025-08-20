<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    public function departments()
    {
        if (!Auth::user()->hasRole('VPAA')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get departments with counts using separate queries to avoid GROUP BY issues
        $departments = Department::all()->map(function ($department) {
            $department->users_count = User::where('department_id', $department->id)->count();
            $department->programs_count = Program::where('department_id', $department->id)->count();
            return $department;
        });

        return view('monitor.departments', compact('departments'));
    }

    public function programs($departmentId)
    {
        if (!Auth::user()->hasRole('VPAA')) {
            abort(403, 'Unauthorized action.');
        }
        
        $department = Department::findOrFail($departmentId);
        
        // Get programs with counts using separate queries
        $programs = Program::where('department_id', $departmentId)->get()->map(function ($program) {
            $program->users_count = User::where('program_id', $program->id)->count();
            $program->faculty_assignments_count = FacultyAssignment::whereHas('user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->count();
            return $program;
        });

        return view('monitor.programs', compact('department', 'programs'));
    }

    public function facultyCompliance($programId)
    {
        if (!Auth::user()->hasRole('VPAA')) {
            abort(403, 'Unauthorized action.');
        }
        
        $program = Program::with('department')->findOrFail($programId);
        $faculty = User::where('program_id', $programId)
            ->where('role_id', 4) // Faculty Member role
            ->with(['facultyAssignments.complianceDocuments.documentType'])
            ->get();

        return view('monitor.faculty-compliance', compact('program', 'faculty'));
    }

    public function faculty()
    {
        if (!Auth::user()->hasRole('Dean')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user = Auth::user();
        
        // Get programs with counts using separate queries
        $programs = Program::where('department_id', $user->department_id)->get()->map(function ($program) {
            $program->users_count = User::where('program_id', $program->id)->count();
            $program->faculty_assignments_count = FacultyAssignment::whereHas('user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->count();
            return $program;
        });

        return view('monitor.faculty', compact('programs'));
    }

    public function programFaculty($programId)
    {
        if (!Auth::user()->hasRole('Dean')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user = Auth::user();
        $program = Program::where('id', $programId)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        $faculty = User::where('program_id', $programId)
            ->where('role_id', 4) // Faculty Member role
            ->with(['facultyAssignments.complianceDocuments.documentType'])
            ->get();

        return view('monitor.program-faculty', compact('program', 'faculty', 'user'));
    }

    public function compliances()
    {
        if (!Auth::user()->hasRole('Program Head')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user = Auth::user();
        $faculty = User::where('program_id', $user->program_id)
            ->where('role_id', 4) // Faculty Member role
            ->with(['facultyAssignments.complianceDocuments.documentType'])
            ->get();

        return view('monitor.compliances', compact('faculty'));
    }

    private function calculateComplianceRate($userId)
    {
        $total = ComplianceDocument::whereHas('assignment', function($q) use ($userId) {
            $q->where('faculty_id', $userId);
        })->count();

        $compiled = ComplianceDocument::whereHas('assignment', function($q) use ($userId) {
            $q->where('faculty_id', $userId);
        })->where('status', 'Compiled')->count();

        return $total > 0 ? round(($compiled / $total) * 100, 1) : 0;
    }
}
