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
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'VPAA') {
            abort(403, 'Unauthorized action.');
        }
        
        // Optimize with eager loading and aggregation
        $departments = Department::withCount(['users', 'programs'])->get();

        return view('monitor.departments', compact('departments'));
    }

    public function programs($departmentId)
    {
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'VPAA') {
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

    public function facultyCompliance(Request $request, $programId)
    {
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'VPAA') {
            abort(403, 'Unauthorized action.');
        }
        
        $program = Program::with('department')->findOrFail($programId);
        
        // Start with base query - VPAA can see all faculty regardless of semester
        $facultyQuery = User::where('program_id', $programId)
            ->whereHas('role', function($query) {
                $query->where('name', 'Faculty Member');
            })
            ->with([
                'facultyAssignments' => function($query) {
                    // VPAA can see all assignments, not filtered by semester
                    $query->orderBy('semester_id', 'desc');
                },
                'facultyAssignments.subject',
                'facultyAssignments.complianceDocuments.documentType',
                'facultyAssignments.complianceDocuments.links'
            ]);

        // Apply filters
        if ($request->filled('faculty_name')) {
            $facultyQuery->where('name', 'like', '%' . $request->faculty_name . '%');
        }

        $faculty = $facultyQuery->get();

        // Filter by subject and compliance status after loading relationships
        if ($request->filled('subject') || $request->filled('compliance_status')) {
            $faculty = $faculty->filter(function ($member) use ($request) {
                $hasMatchingData = false;
                
                foreach ($member->facultyAssignments as $assignment) {
                    $subjectMatch = true;
                    if ($request->filled('subject')) {
                        $subjectText = $assignment->subject 
                            ? $assignment->subject->code . ' - ' . $assignment->subject->title
                            : $assignment->subject_code . ' - ' . $assignment->subject_description;
                        $subjectMatch = stripos($subjectText, $request->subject) !== false;
                    }
                    
                    if ($subjectMatch) {
                        if ($request->filled('compliance_status')) {
                            foreach ($assignment->complianceDocuments as $document) {
                                if ($document->status === $request->compliance_status) {
                                    $hasMatchingData = true;
                                    break 2;
                                }
                            }
                        } else {
                            $hasMatchingData = true;
                            break;
                        }
                    }
                }
                
                return $hasMatchingData;
            });
        }

        return view('monitor.faculty-compliance', compact('program', 'faculty'));
    }

    public function faculty()
    {
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'Dean') {
            abort(403, 'Unauthorized action.');
        }
        
        // Get programs with counts using separate queries
        $programs = Program::where('department_id', $user->department_id)->get()->map(function ($program) {
            $program->users_count = User::where('program_id', $program->id)
                ->whereHas('role', function($query) {
                    $query->where('name', 'Faculty Member');
                })->count();
            $program->faculty_assignments_count = FacultyAssignment::whereHas('user', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })->count();
            return $program;
        });

        return view('monitor.faculty', compact('programs'));
    }

    public function programFaculty(Request $request, $programId)
    {
        $user = Auth::user();
        
        // Check if user is Dean
        if (!$user->role || $user->role->name !== 'Dean') {
            abort(403, 'Unauthorized action.');
        }
        
        $program = Program::where('id', $programId)
            ->where('department_id', $user->department_id)
            ->firstOrFail();

        // Start with base query for faculty users only
        $facultyQuery = User::where('program_id', $programId)
            ->whereHas('role', function($query) {
                $query->where('name', 'Faculty Member');
            })
            ->with([
                'facultyAssignments' => function($query) use ($user) {
                    // Filter by current semester for the dean
                    $query->where('semester_id', $user->current_semester_id);
                },
                'facultyAssignments.subject',
                'facultyAssignments.complianceDocuments.documentType',
                'facultyAssignments.complianceDocuments.links'
            ]);

        // Apply faculty name filter
        if ($request->filled('faculty_name')) {
            $facultyQuery->where('name', 'like', '%' . $request->faculty_name . '%');
        }

        $faculty = $facultyQuery->get();
        
        // Filter by subject and compliance status after loading relationships
        if ($request->filled('subject') || $request->filled('compliance_status')) {
            $faculty = $faculty->filter(function ($member) use ($request) {
                $hasMatchingData = false;
                
                foreach ($member->facultyAssignments as $assignment) {
                    $subjectMatch = true;
                    if ($request->filled('subject')) {
                        $subjectText = $assignment->subject 
                            ? $assignment->subject->code . ' - ' . $assignment->subject->title
                            : $assignment->subject_code . ' - ' . $assignment->subject_description;
                        $subjectMatch = stripos($subjectText, $request->subject) !== false;
                    }
                    
                    if ($subjectMatch) {
                        if ($request->filled('compliance_status')) {
                            foreach ($assignment->complianceDocuments as $document) {
                                if ($document->status === $request->compliance_status) {
                                    $hasMatchingData = true;
                                    break 2;
                                }
                            }
                        } else {
                            $hasMatchingData = true;
                            break;
                        }
                    }
                }
                
                return $hasMatchingData;
            });
        }

        return view('monitor.program-faculty', compact('program', 'faculty', 'user'));
    }

    public function compliances(Request $request)
    {
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'Program Head') {
            abort(403, 'Unauthorized action.');
        }
        
        $query = User::where('program_id', $user->program_id)
            ->whereHas('role', function($query) {
                $query->where('name', 'Faculty Member');
            });

        // Apply faculty name filter
        if ($request->filled('faculty_name')) {
            $query->where('name', 'like', '%' . $request->get('faculty_name') . '%');
        }

        $faculty = $query->with([
            'facultyAssignments' => function($assignmentQuery) use ($user, $request) {
                $assignmentQuery->where('semester_id', $user->current_semester_id);
                
                // Apply subject filter
                if ($request->filled('subject')) {
                    $assignmentQuery->where(function($subQuery) use ($request) {
                        $subQuery->where('subject_code', 'like', '%' . $request->get('subject') . '%')
                                ->orWhere('subject_description', 'like', '%' . $request->get('subject') . '%');
                    });
                }
            },
            'facultyAssignments.subject',
            'facultyAssignments.complianceDocuments' => function($docQuery) use ($request) {
                // Apply compliance status filter
                if ($request->filled('compliance_status')) {
                    $docQuery->where('status', $request->get('compliance_status'));
                }
            },
            'facultyAssignments.complianceDocuments.documentType',
            'facultyAssignments.complianceDocuments.links'
        ])->get();

        return view('monitor.compliances', compact('faculty'));
    }

    private function calculateComplianceRate($userId)
    {
        $total = ComplianceDocument::whereHas('assignment', function($q) use ($userId) {
            $q->where('faculty_id', $userId);
        })->count();

        $complied = ComplianceDocument::whereHas('assignment', function($q) use ($userId) {
            $q->where('faculty_id', $userId);
        })->where('status', 'Complied')->count();

        return $total > 0 ? round(($complied / $total) * 100, 1) : 0;
    }
}
