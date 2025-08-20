<?php

namespace App\Http\Controllers;

use App\Models\FacultyAssignment;
use App\Models\User;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultyAssignmentController extends Controller
{
    public function index()
    {
        $faculty = User::whereHas('role', fn($q) => $q->where('name', 'Faculty Member'))->orderBy('name')->get();
        $subjects = Subject::orderBy('code')->get();
        $terms = Term::orderByDesc('id')->get();
        $assignments = FacultyAssignment::with(['subject','term','user'])
            ->orderByDesc('id')->get();
        return view('assignments.index', compact('faculty','subjects','terms','assignments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => ['required','exists:users,id'],
            'subject_id' => ['required','exists:subjects,id'],
            'term_id' => ['required','exists:terms,id'],
        ]);

        $exists = FacultyAssignment::where($validated)->exists();
        if ($exists) {
            return back()->withErrors(['assignment' => 'Duplicate assignment.']);
        }

        FacultyAssignment::create($validated);
        return back()->with('status', 'Assignment created.');
    }
}


