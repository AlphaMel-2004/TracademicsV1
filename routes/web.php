<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
});

// Local auth routes (email/password restricted to brokenshire domain)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Google OAuth routes
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->with(['hd' => 'brokenshire.edu.ph'])->redirect();
})->name('google.redirect');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();

    if (!str_ends_with(strtolower($googleUser->getEmail()), '@brokenshire.edu.ph')) {
        return redirect('/')->withErrors(['email' => 'Only @brokenshire.edu.ph accounts are allowed.']);
    }

    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName() ?? $googleUser->getNickname(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => '\\',
        ]
    );

    Auth::login($user, true);

    return redirect()->route('dashboard');
})->name('google.callback');

// Dashboard (with semester session middleware)
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'semester.session', 'activity.logger'])
    ->name('dashboard');

// Semester Selection Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/semester/select', [\App\Http\Controllers\SemesterController::class, 'select'])->name('semester.select');
    Route::post('/semester/set', [\App\Http\Controllers\SemesterController::class, 'setSemester'])->name('semester.set');
    Route::get('/semester/change', [\App\Http\Controllers\SemesterController::class, 'changeSemester'])->name('semester.change');
});

// MIS Routes - Only accessible by MIS role
Route::middleware(['auth', 'role:MIS', 'activity.logger'])->prefix('mis')->name('mis.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\MISController::class, 'dashboard'])->name('dashboard');
    Route::get('/departments', [\App\Http\Controllers\MISController::class, 'departments'])->name('departments');
    Route::post('/departments', [\App\Http\Controllers\MISController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [\App\Http\Controllers\MISController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [\App\Http\Controllers\MISController::class, 'destroyDepartment'])->name('departments.delete');
    Route::get('/programs', [\App\Http\Controllers\MISController::class, 'programs'])->name('programs');
    Route::post('/programs', [\App\Http\Controllers\MISController::class, 'storeProgram'])->name('programs.store');
    Route::put('/programs/{program}', [\App\Http\Controllers\MISController::class, 'updateProgram'])->name('programs.update');
    Route::delete('/programs/{program}', [\App\Http\Controllers\MISController::class, 'destroyProgram'])->name('programs.delete');
    Route::get('/users', [\App\Http\Controllers\MISController::class, 'users'])->name('users');
    Route::post('/users', [\App\Http\Controllers\MISController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\MISController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\MISController::class, 'deleteUser'])->name('users.delete');
    Route::get('/user-logs', [\App\Http\Controllers\MISController::class, 'userLogs'])->name('user-logs');
    Route::get('/curriculum', [\App\Http\Controllers\MISController::class, 'curriculum'])->name('curriculum');
    Route::get('/curriculum/{curriculum}/details', [\App\Http\Controllers\MISController::class, 'curriculumDetails'])->name('curriculum.details');
    Route::post('/curriculum/{course}/load', [\App\Http\Controllers\MISController::class, 'loadCurriculumSubjects'])->name('curriculum.load');
    Route::get('/semesters', [\App\Http\Controllers\MISController::class, 'semesters'])->name('semesters');
    Route::post('/semesters', [\App\Http\Controllers\MISController::class, 'storeSemester'])->name('semesters.store');
    Route::put('/semesters/{semester}', [\App\Http\Controllers\MISController::class, 'updateSemester'])->name('semesters.update');
    Route::delete('/semesters/{semester}', [\App\Http\Controllers\MISController::class, 'destroySemester'])->name('semesters.delete');
});

// VPAA Routes - Only accessible by VPAA role
Route::middleware(['auth', 'role:VPAA', 'semester.session', 'activity.logger'])->group(function () {
    Route::get('/departments', [\App\Http\Controllers\MonitorController::class, 'departments'])->name('monitor.departments');
    Route::get('/departments/{department}/programs', [\App\Http\Controllers\MonitorController::class, 'programs'])->name('monitor.programs');
    Route::get('/programs/{program}/faculty', [\App\Http\Controllers\MonitorController::class, 'facultyCompliance'])->name('monitor.faculty-compliance');
    Route::get('/programs/{program}/faculty/{faculty}', [\App\Http\Controllers\MonitorController::class, 'vpaaFacultyDetail'])->name('monitor.vpaa-faculty-detail');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    
    // API routes for dashboard filters
    Route::get('/api/departments/{department}/programs', [\App\Http\Controllers\DashboardController::class, 'getDepartmentPrograms']);
    Route::get('/api/compliance/data', [\App\Http\Controllers\DashboardController::class, 'getComplianceData']);
});

// Dean Routes - Only accessible by Dean role
Route::middleware(['auth', 'role:Dean', 'semester.session', 'activity.logger'])->group(function () {
    Route::get('/monitor/faculty', [\App\Http\Controllers\MonitorController::class, 'faculty'])->name('monitor.faculty');
    Route::get('/monitor/faculty/programs/{program}', [\App\Http\Controllers\MonitorController::class, 'programFaculty'])->name('monitor.program-faculty');
});

// Program Head Routes - Only accessible by Program Head role
Route::middleware(['auth', 'role:Program Head', 'semester.session', 'activity.logger'])->group(function () {
    Route::get('/monitor/compliances', [\App\Http\Controllers\MonitorController::class, 'compliances'])->name('monitor.compliances');
    Route::get('/monitor/faculty/{faculty}', [\App\Http\Controllers\MonitorController::class, 'facultyDetail'])->name('monitor.faculty-detail');
    Route::get('/faculty-load', [\App\Http\Controllers\FacultyAssignmentController::class, 'index'])->name('assignments.index');
});

// Faculty Routes - Only accessible by Faculty Member role
Route::middleware(['auth', 'role:Faculty Member', 'semester.session', 'activity.logger'])->group(function () {
    Route::get('/compliance', [\App\Http\Controllers\ComplianceController::class, 'index'])->name('compliances.index');
    Route::get('/compliance/{subject}', [\App\Http\Controllers\ComplianceController::class, 'subject'])->name('compliance.subject');
    Route::post('/compliance/link/submit', [\App\Http\Controllers\ComplianceController::class, 'submitLink'])->name('compliance.link.submit');
    Route::delete('/compliance/link/{link}', [\App\Http\Controllers\ComplianceController::class, 'deleteLink'])->name('compliance.link.delete');
    Route::post('/compliance/mark-not-applicable/{documentType}', [\App\Http\Controllers\ComplianceController::class, 'markAsNotApplicable'])->name('compliance.mark-not-applicable');
    Route::post('/compliance/unmark-not-applicable/{documentType}', [\App\Http\Controllers\ComplianceController::class, 'unmarkNotApplicable'])->name('compliance.unmark-not-applicable');
    // Legacy routes for backward compatibility
    Route::post('/compliance/{subject}/submit', [\App\Http\Controllers\ComplianceController::class, 'submitDocument'])->name('compliance.submit');
    Route::delete('/compliance/{submission}', [\App\Http\Controllers\ComplianceController::class, 'deleteSubmission'])->name('compliance.delete');
});

// Common Routes - Accessible by all authenticated users except MIS
Route::middleware(['auth', 'role:VPAA,Dean,Program Head,Faculty Member', 'semester.session', 'activity.logger'])->group(function () {
    // Other common routes can go here
});

// Profile Routes - Accessible by ALL authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/settings', [\App\Http\Controllers\ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Assignment/Load routes - Accessible by Program Head only
Route::middleware(['auth', 'role:Program Head', 'semester.session', 'activity.logger'])->group(function () {
    Route::post('/assignments', [\App\Http\Controllers\FacultyAssignmentController::class, 'store'])->name('assignments.store');
});

// Compliance routes - Accessible by Faculty Member only
Route::middleware(['auth', 'role:Faculty Member', 'semester.session', 'activity.logger'])->group(function () {
    Route::post('/compliances', [\App\Http\Controllers\ComplianceController::class, 'store'])->name('compliances.store');
    Route::post('/compliances/document', [\App\Http\Controllers\ComplianceController::class, 'saveDocument'])->name('compliances.document.save');
});

// Report routes - Accessible by VPAA, Dean, and Program Head
Route::middleware(['auth', 'role:VPAA,Dean,Program Head', 'semester.session', 'activity.logger'])->group(function () {
    Route::get('/reports/export/excel', [\App\Http\Controllers\ExportController::class, 'excel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [\App\Http\Controllers\ExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/program-monitoring', [\App\Http\Controllers\ReportController::class, 'programMonitoring'])->name('program.monitoring');
});

// Logout route
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Test routes for role middleware (remove in production)
Route::middleware(['auth', 'role:MIS'])->get('/test-mis', function () {
    return 'MIS role access successful!';
})->name('test.mis');

Route::middleware(['auth', 'role:VPAA'])->get('/test-vpaa', function () {
    return 'VPAA role access successful!';
})->name('test.vpaa');

Route::middleware(['auth', 'role:VPAA,Dean'])->get('/test-multi', function () {
    return 'Multi-role access successful!';
})->name('test.multi');

Route::middleware(['auth', 'role:Faculty Member'])->get('/test-faculty', function () {
    $user = Auth::user();
    return 'Faculty role access successful! User: ' . $user->name . ', Role: ' . ($user->role ? $user->role->name : 'No Role');
})->name('test.faculty');

// Debug route to check current user role
Route::middleware(['auth'])->get('/debug-role', function () {
    $user = Auth::user();
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'role_id' => $user->role_id,
        'role_name' => $user->role ? $user->role->name : 'No Role',
        'department' => $user->department ? $user->department->name : 'No Department',
        'program' => $user->program ? $user->program->name : 'No Program'
    ]);
})->name('debug.role');
