<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Database for Faculty Users\n";
echo "================================\n\n";

// Check total users
$totalUsers = App\Models\User::count();
echo "Total Users: " . $totalUsers . "\n\n";

// Check roles
echo "Roles and User Counts:\n";
$roles = App\Models\Role::with('users')->get();
foreach ($roles as $role) {
    echo "- " . $role->name . ": " . $role->users->count() . " users\n";
}

echo "\nFaculty Members:\n";
$faculty = App\Models\User::whereHas('role', function($q) { 
    $q->where('name', 'Faculty Member'); 
})->get();

if ($faculty->count() > 0) {
    foreach ($faculty as $user) {
        echo "- " . $user->name . " (ID: " . $user->id . ", Program ID: " . $user->program_id . ")\n";
    }
} else {
    echo "No faculty members found!\n";
}

echo "\nPrograms with Faculty Count:\n";
$programs = App\Models\Program::withCount(['users' => function($q) {
    $q->whereHas('role', function($role) { 
        $role->where('name', 'Faculty Member'); 
    });
}])->get();

foreach ($programs as $program) {
    echo "- " . $program->name . " (ID: " . $program->id . "): " . $program->users_count . " faculty\n";
}

echo "\nSample VPAA Query for Program ID 7:\n";
$programId = 7;
$facultyInProgram = App\Models\User::where('program_id', $programId)
    ->whereHas('role', function($query) {
        $query->where('name', 'Faculty Member');
    })
    ->with(['facultyAssignments.subject', 'complianceDocuments'])
    ->get();

echo "Faculty in Program 7: " . $facultyInProgram->count() . "\n";
if ($facultyInProgram->count() > 0) {
    foreach ($facultyInProgram as $faculty) {
        echo "  - " . $faculty->name . "\n";
    }
}
