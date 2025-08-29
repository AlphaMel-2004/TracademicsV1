<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Semester;
use App\Models\SemesterSession;
use Illuminate\Support\Facades\Auth;

class SemesterController extends Controller
{
    public function select()
    {
        // Don't show semester selection for MIS users
        if (Auth::user()->role && Auth::user()->role->name === 'MIS') {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $semesters = Semester::where('is_active', true)->orderBy('year', 'desc')->orderBy('name', 'desc')->get();
        $currentSemester = $user->current_semester_id ? Semester::find($user->current_semester_id) : null;
        
        return view('semester.select', compact('semesters', 'currentSemester'));
    }

    public function setSemester(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $user = Auth::user();
        $semester = Semester::findOrFail($request->semester_id);

        // Close any existing active sessions for this user
        SemesterSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'logged_out_at' => now(),
            ]);

        // Set the current semester for the user
        $user->current_semester_id = $semester->id;
        $user->save();

        // Create new semester session
        SemesterSession::create([
            'user_id' => $user->id,
            'semester_id' => $semester->id,
            'logged_in_at' => now(),
            'is_active' => true,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "You are now working in {$semester->name} {$semester->year}");
    }

    public function changeSemester()
    {
        $user = Auth::user();
        $currentSemesterName = '';
        
        // Get current semester name for messaging
        if ($user->current_semester_id) {
            $currentSemester = Semester::find($user->current_semester_id);
            if ($currentSemester) {
                $currentSemesterName = "{$currentSemester->name} {$currentSemester->year}";
            }
        }
        
        // Close current session
        if ($user->current_semester_id) {
            SemesterSession::where('user_id', $user->id)
                ->where('semester_id', $user->current_semester_id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                ]);
        }

        // Clear current semester
        $user->current_semester_id = null;
        $user->save();

        $message = $currentSemesterName 
            ? "You have logged out of {$currentSemesterName}. Please select a new semester to continue."
            : "Please select a semester to work with.";

        return redirect()->route('semester.select')->with('info', $message);
    }
}
