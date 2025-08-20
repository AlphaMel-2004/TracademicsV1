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

// Dashboard
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// VPAA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/monitor/departments', [\App\Http\Controllers\MonitorController::class, 'departments'])->name('monitor.departments');
    Route::get('/monitor/departments/{department}/programs', [\App\Http\Controllers\MonitorController::class, 'programs'])->name('monitor.programs');
    Route::get('/monitor/programs/{program}/faculty', [\App\Http\Controllers\MonitorController::class, 'facultyCompliance'])->name('monitor.faculty-compliance');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
});

// Dean Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/monitor/faculty', [\App\Http\Controllers\MonitorController::class, 'faculty'])->name('monitor.faculty');
    Route::get('/monitor/faculty/programs/{program}', [\App\Http\Controllers\MonitorController::class, 'programFaculty'])->name('monitor.program-faculty');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
});

// Program Head Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/monitor/compliances', [\App\Http\Controllers\MonitorController::class, 'compliances'])->name('monitor.compliances');
    Route::get('/assignments', [\App\Http\Controllers\FacultyAssignmentController::class, 'index'])->name('assignments.index');
});

// Faculty Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/subjects', [\App\Http\Controllers\SubjectsController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{assignment}', [\App\Http\Controllers\SubjectsController::class, 'show'])->name('subjects.show');
    Route::post('/subjects/{assignment}/submit', [\App\Http\Controllers\SubjectsController::class, 'submitDocument'])->name('subjects.submit');
    Route::get('/compliances', [\App\Http\Controllers\ComplianceController::class, 'index'])->name('compliances.index');
});

// Common Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile/settings', [\App\Http\Controllers\ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Existing routes
    Route::post('/assignments', [\App\Http\Controllers\FacultyAssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/compliances', [\App\Http\Controllers\ComplianceController::class, 'store'])->name('compliances.store');
    Route::post('/compliances/document', [\App\Http\Controllers\ComplianceController::class, 'saveDocument'])->name('compliances.document.save');
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
