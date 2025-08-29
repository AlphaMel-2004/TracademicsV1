<?php

namespace App\Http\Controllers;

use App\Models\FacultyAssignment;
use App\Models\User;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\CurriculumSubject;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacultyAssignmentController extends Controller
{
    public function index()
    {
        // Only Program Heads can access this
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'Program Head') {
            abort(403, 'Access denied.');
        }

        $programId = $user->program_id;
        
        // Get faculty in the same program
        $faculty = User::where('program_id', $programId)
            ->whereHas('role', fn($q) => $q->where('name', 'Faculty Member'))
            ->orderBy('name')
            ->get();

        // Get the user's program to find the department
        $userProgram = Auth::user()->program;
        
        if (!$userProgram) {
            abort(403, 'No program assigned to your account. Please contact the administrator.');
        }
        
        // In the system, Program and Course are now equivalent entities
        // But we use program_id for backwards compatibility (as noted in Course model)
        $courseId = $userProgram->id;
        
        // Get available subjects only from the curriculum for the specific program/course
        $availableSubjects = CurriculumSubject::whereHas('curriculum', function($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->orderBy('subject_code')->get();

        // Transform curriculum subjects to look like regular subjects for the view
        $subjects = $availableSubjects->map(function($curriculumSubject) {
            return (object) [
                'id' => $curriculumSubject->id,
                'code' => $curriculumSubject->subject_code,
                'title' => $curriculumSubject->subject_description,
            ];
        });

        // Get available terms (using semesters as terms for now)
        $terms = Semester::where('is_active', true)->get();

        // Get current semester
        $currentSemester = Auth::user()->currentSemester;
        
        // Get current loads for the program (rename to assignments for view)
        $assignments = FacultyAssignment::with(['user', 'semester'])
            ->whereHas('user', function($q) use ($programId) {
                $q->where('program_id', $programId);
            })
            ->where('semester_id', $currentSemester->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('assignments.index', compact('faculty', 'subjects', 'terms', 'assignments', 'currentSemester'));
    }

    public function store(Request $request)
    {
        // Only Program Heads can create loads
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'Program Head') {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'faculty_id' => ['required', 'exists:users,id'],
            'subject_id' => ['required', 'exists:curriculum_subjects,id'],
            'term_id' => ['required', 'exists:semesters,id'],
        ]);

        $currentSemester = Auth::user()->currentSemester;
        $programId = Auth::user()->program_id;

        // Get the selected curriculum subject
        $curriculumSubject = CurriculumSubject::findOrFail($validated['subject_id']);
        
        // Verify that the curriculum subject belongs to this program's curriculum
        $belongsToProgram = $curriculumSubject->whereHas('curriculum', function($q) use ($programId) {
            $q->where('course_id', $programId);
        })->exists();
        
        if (!$belongsToProgram) {
            return back()->withErrors(['subject_id' => 'Invalid subject selection. Subject does not belong to your program curriculum.']);
        }

        // Verify faculty belongs to the same program
        $faculty = User::where('id', $validated['faculty_id'])
            ->where('program_id', $programId)
            ->first();
            
        if (!$faculty) {
            return back()->withErrors(['faculty_id' => 'Invalid faculty selection.']);
        }

        // Check for duplicate assignment
        $exists = FacultyAssignment::where([
            'faculty_id' => $validated['faculty_id'],
            'subject_code' => $curriculumSubject->subject_code,
            'semester_id' => $validated['term_id'],
        ])->exists();
        
        if ($exists) {
            return back()->withErrors(['assignment' => 'Faculty is already assigned to this subject for the selected term.']);
        }

        // Create the assignment
        FacultyAssignment::create([
            'faculty_id' => $validated['faculty_id'],
            'subject_code' => $curriculumSubject->subject_code,
            'subject_description' => $curriculumSubject->subject_description,
            'semester_id' => $validated['term_id'],
            'program_id' => $programId,
            'status' => 'Active',
        ]);

        return back()->with('status', 'Subject load assigned successfully.');
    }

    public function destroy(FacultyAssignment $assignment)
    {
        // Only Program Heads can delete loads from their program
        $user = Auth::user();
        if (!$user->role || $user->role->name !== 'Program Head' || $assignment->program_id !== $user->program_id) {
            abort(403, 'Access denied.');
        }

        $assignment->delete();
        return back()->with('status', 'Subject load removed successfully.');
    }
}


