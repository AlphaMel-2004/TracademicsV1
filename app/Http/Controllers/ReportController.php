<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has access to reports
        if (!$user->hasRole('VPAA') && !$user->hasRole('Dean')) {
            abort(403, 'Unauthorized access to reports.');
        }

        $data = [];
        
        if ($user->hasRole('VPAA')) {
            // VPAA sees institution-wide reports
            $data = [
                'type' => 'vpaa',
                'total_faculty' => User::where('role_id', 4)->count(),
                'total_assignments' => FacultyAssignment::count(),
                'compliance_rate' => $this->calculateOverallComplianceRate(),
            ];
        } elseif ($user->hasRole('Dean')) {
            // Dean sees department-level reports
            $data = [
                'type' => 'dean',
                'department' => $user->department,
                'total_faculty' => User::where('department_id', $user->department_id)->where('role_id', 4)->count(),
                'compliance_rate' => $this->calculateDepartmentComplianceRate($user->department_id),
            ];
        }

        return view('reports.index', compact('data', 'user'));
    }

    public function programMonitoring()
    {
        $user = Auth::user();
        
        // Check if user has access to program monitoring
        if (!$user->hasRole('VPAA') && !$user->hasRole('Dean') && !$user->hasRole('Program Head')) {
            abort(403, 'Unauthorized access to program monitoring.');
        }

        return view('reports.program-monitoring');
    }

    private function calculateOverallComplianceRate()
    {
        $total = ComplianceDocument::count();
        $complied = ComplianceDocument::where('status', 'Complied')->count();

        return $total > 0 ? round(($complied / $total) * 100, 1) : 0;
    }

    private function calculateDepartmentComplianceRate($departmentId)
    {
        $total = ComplianceDocument::whereHas('assignment.user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        })->count();
        
        $complied = ComplianceDocument::whereHas('assignment.user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        })->where('status', 'Complied')->count();
        
        return $total > 0 ? round(($complied / $total) * 100, 1) : 0;
    }
}


