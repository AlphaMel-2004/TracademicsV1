<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ComplianceDocument;
use App\Models\FacultyAssignment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function excel()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('VPAA') && !$user->hasRole('Dean')) {
            abort(403, 'Unauthorized access to exports.');
        }

        $data = $this->getExportData($user);
        
        // Generate Excel file with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = 'compliance_report_' . $timestamp . '.xlsx';
        
        // Here you would implement actual Excel generation
        // For now, return a response indicating the file would be generated
        return response()->json([
            'message' => 'Excel export would generate: ' . $filename,
            'data' => $data
        ]);
    }

    public function pdf()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('VPAA') && !$user->hasRole('Dean')) {
            abort(403, 'Unauthorized access to exports.');
        }

        $data = $this->getExportData($user);
        
        // Generate PDF file with timestamp in filename
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = 'compliance_report_' . $timestamp . '.pdf';
        
        // Here you would implement actual PDF generation
        // For now, return a response indicating the file would be generated
        return response()->json([
            'message' => 'PDF export would generate: ' . $filename,
            'data' => $data
        ]);
    }

    private function getExportData($user)
    {
        if ($user->hasRole('VPAA')) {
            // Institution-wide data
            return [
                'type' => 'vpaa',
                'total_faculty' => User::where('role_id', 4)->count(),
                'total_assignments' => FacultyAssignment::count(),
                'compliance_rate' => $this->calculateOverallComplianceRate(),
                'departments' => $this->getDepartmentData(),
                'generated_at' => Carbon::now()->format('F d, Y \a\t g:i A'),
                'generated_by' => $user->name
            ];
        } elseif ($user->hasRole('Dean')) {
            // Department-level data
            return [
                'type' => 'dean',
                'department' => $user->department,
                'total_faculty' => User::where('department_id', $user->department_id)->where('role_id', 4)->count(),
                'compliance_rate' => $this->calculateDepartmentComplianceRate($user->department_id),
                'programs' => $this->getProgramData($user->department_id),
                'generated_at' => Carbon::now()->format('F d, Y \a\t g:i A'),
                'generated_by' => $user->name
            ];
        }
        
        return [];
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

    private function getDepartmentData()
    {
        return DB::table('departments')
            ->select('departments.*')
            ->selectRaw('COUNT(DISTINCT users.id) as faculty_count')
            ->selectRaw('COUNT(DISTINCT faculty_assignments.id) as assignment_count')
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
            ->groupBy('departments.id', 'departments.name')
            ->get();
    }

    private function getProgramData($departmentId)
    {
        return DB::table('programs')
            ->select('programs.*')
            ->selectRaw('COUNT(DISTINCT users.id) as faculty_count')
            ->selectRaw('COUNT(DISTINCT faculty_assignments.id) as assignment_count')
            ->where('programs.department_id', $departmentId)
            ->leftJoin('users', 'programs.id', '=', 'users.program_id')
            ->leftJoin('faculty_assignments', 'users.id', '=', 'faculty_assignments.faculty_id')
            ->groupBy('programs.id', 'programs.name')
            ->get();
    }
}


