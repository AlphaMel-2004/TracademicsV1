<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\UserActivityLog;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MISController extends Controller
{
    private function checkMISAccess()
    {
        if (!Auth::user()->role || Auth::user()->role->name !== 'MIS') {
            abort(403, 'Unauthorized access.');
        }
    }

    public function dashboard()
    {
        $this->checkMISAccess();
        $stats = [
            'total_users' => User::count(),
            'total_departments' => Department::count(),
            'total_programs' => Program::count(),
            'active_semesters' => Semester::where('is_active', true)->count(),
            'recent_activities' => UserActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('mis.dashboard', compact('stats'));
    }

    public function departments()
    {
        $this->checkMISAccess();
        $departments = Department::withCount(['programs', 'users'])->get();
        
        return view('mis.departments', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        $department = Department::create($request->only('name'));

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'Created department: ' . $request->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.departments')
            ->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $oldName = $department->name;
        $department->update($request->only('name'));

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated department from '{$oldName}' to '{$request->name}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.departments')
            ->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        $this->checkMISAccess();
        
        // Soft delete by updating name to indicate deletion
        $department->update(['name' => $department->name . ' (Deleted)']);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => 'Soft deleted department: ' . $department->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('mis.departments')
            ->with('success', 'Department deleted successfully.');
    }

    public function programs()
    {
        $this->checkMISAccess();
        $programs = Program::with('department')->withCount('users')->get();
        $departments = Department::all();
        return view('mis.programs', compact('programs', 'departments'));
    }

    public function storeProgram(Request $request)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $program = Program::create($request->only(['name', 'description', 'department_id']));

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'Created program: ' . $request->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.programs')
            ->with('success', 'Program created successfully.');
    }

    public function updateProgram(Request $request, Program $program)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $oldName = $program->name;
        $program->update($request->only(['name', 'description', 'department_id']));

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated program from '{$oldName}' to '{$request->name}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.programs')
            ->with('success', 'Program updated successfully.');
    }

    public function destroyProgram(Program $program)
    {
        $this->checkMISAccess();
        
        // Check if program has users
        if ($program->users()->count() > 0) {
            return redirect()->route('mis.programs')
                ->with('error', 'Cannot delete program. It has users assigned.');
        }

        $program->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => 'Deleted program: ' . $program->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('mis.programs')
            ->with('success', 'Program deleted successfully.');
    }

    public function users()
    {
        $this->checkMISAccess();
        $users = User::with(['role', 'department', 'program'])
            ->where('id', '!=', Auth::id()) // Exclude current MIS user
            ->paginate(20);
        
        $roles = Role::where('name', '!=', 'MIS')->get();
        $departments = Department::all();
        $programs = Program::all();

        // Calculate statistics
        $totalUsers = User::where('id', '!=', Auth::id())->count();
        $facultyCount = User::whereHas('role', function($query) {
            $query->where('name', 'Faculty Member');
        })->where('id', '!=', Auth::id())->count();
        $adminCount = User::whereHas('role', function($query) {
            $query->whereIn('name', ['VPAA', 'Dean', 'Program Head']);
        })->where('id', '!=', Auth::id())->count();
        $activeCount = User::whereNotNull('email_verified_at')->where('id', '!=', Auth::id())->count();

        $stats = [
            'total' => $totalUsers,
            'faculty' => $facultyCount,
            'admin' => $adminCount,
            'active' => $activeCount
        ];

        return view('mis.users', compact('users', 'roles', 'departments', 'programs', 'stats'));
    }

    public function storeUser(Request $request)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => 'nullable|exists:programs,id',
            'faculty_type' => 'nullable|in:Full-time,Part-time',
        ]);

        $userData = $request->only('name', 'email', 'role_id', 'department_id', 'program_id', 'faculty_type');
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'Created user: ' . $request->name . ' (' . $request->email . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.users')
            ->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => 'nullable|exists:programs,id',
            'faculty_type' => 'nullable|in:Full-time,Part-time',
        ]);

        $oldData = $user->name . ' (' . $user->email . ')';
        $user->update($request->only(['name', 'email', 'role_id', 'department_id', 'program_id', 'faculty_type']));

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated user: {$oldData} to " . $request->name . ' (' . $request->email . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.users')
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        $this->checkMISAccess();
        if ($user->id === Auth::id()) {
            return redirect()->route('mis.users')
                ->with('error', 'Cannot delete your own account.');
        }

        $userName = $user->name . ' (' . $user->email . ')';
        $user->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => 'Deleted user: ' . $userName,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('mis.users')
            ->with('success', 'User deleted successfully.');
    }

    public function userLogs()
    {
        $this->checkMISAccess();
        $logs = UserActivityLog::with('user')
            ->latest()
            ->paginate(50);

        $users = User::select('id', 'name')->get();

        return view('mis.user-logs', compact('logs', 'users'));
    }

    public function curriculum()
    {
        $this->checkMISAccess();
        $courses = Course::withCount('curriculums')->with(['curriculums.subjects'])->get();
        $departments = Department::all();
        
        // Calculate accurate counts
        $totalCourses = $courses->count();
        $totalCurriculums = $courses->sum('curriculums_count');
        
        // Get unique subjects count using subject_code (not subject_id)
        $curriculumSubjectsCount = CurriculumSubject::distinct('subject_code')->count('subject_code');
        
        return view('mis.curriculum', compact('courses', 'departments', 'totalCourses', 'totalCurriculums', 'curriculumSubjectsCount'));
    }

    public function curriculumDetails(Curriculum $curriculum)
    {
        $this->checkMISAccess();
        
        // Load the curriculum with its subjects grouped by year and semester
        $curriculum->load(['subjects' => function($query) {
            $query->orderBy('year_level', 'asc')->orderBy('semester', 'asc')->orderBy('subject_code', 'asc');
        }, 'course']);
        
        // Group subjects by year and semester for better display
        $subjectsByYear = $curriculum->subjects->groupBy('year_level');
        
        return response()->json([
            'success' => true,
            'curriculum' => $curriculum,
            'subjects_by_year' => $subjectsByYear,
            'html' => view('mis.partials.curriculum-details', compact('curriculum', 'subjectsByYear'))->render()
        ]);
    }

    public function loadCurriculumSubjects(Course $course)
    {
        $this->checkMISAccess();
        // This would trigger the curriculum seeder for a specific course
        // Implementation depends on specific requirements
        
        return redirect()->route('mis.curriculum')
            ->with('success', 'Curriculum subjects loaded successfully.');
    }

    public function semesters()
    {
        $this->checkMISAccess();
        $semesters = Semester::latest()->get();
        return view('mis.semesters', compact('semesters'));
    }

    public function storeSemester(Request $request)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:255',
        ]);

        // If this semester is set as active, deactivate all others
        if ($request->has('is_active')) {
            Semester::where('is_active', true)->update(['is_active' => false]);
        }

        $semester = Semester::create([
            'name' => $request->name,
            'year' => $request->year,
            'is_active' => $request->has('is_active'),
        ]);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'Created semester: ' . $request->name . ' (' . $request->year . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.semesters')
            ->with('success', 'Semester created successfully.');
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $this->checkMISAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:255',
        ]);

        // If this semester is set as active, deactivate all others
        if ($request->has('is_active')) {
            Semester::where('is_active', true)->where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $oldData = $semester->name . ' (' . $semester->year . ')';
        $semester->update([
            'name' => $request->name,
            'year' => $request->year,
            'is_active' => $request->has('is_active'),
        ]);

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => "Updated semester from '{$oldData}' to " . $request->name . ' (' . $request->year . ')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('mis.semesters')
            ->with('success', 'Semester updated successfully.');
    }

    public function destroySemester(Semester $semester)
    {
        $this->checkMISAccess();
        
        // Check if semester has faculty assignments
        if ($semester->facultyAssignments()->count() > 0) {
            return redirect()->route('mis.semesters')
                ->with('error', 'Cannot delete semester. It has faculty assignments.');
        }

        $semesterName = $semester->name . ' (' . $semester->year . ')';
        $semester->delete();

        // Log activity
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => 'Deleted semester: ' . $semesterName,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('mis.semesters')
            ->with('success', 'Semester deleted successfully.');
    }
}
