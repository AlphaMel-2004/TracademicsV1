<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Data Check ===\n";
echo "Departments: " . App\Models\Department::count() . "\n";
echo "Programs: " . App\Models\Program::count() . "\n";
echo "Users: " . App\Models\User::count() . "\n";

echo "\n=== Faculty Type Data ===\n";
$users = App\Models\User::whereNotNull('faculty_type')->get(['name', 'faculty_type', 'department_id']);
foreach($users as $user) {
    echo $user->name . " - " . $user->faculty_type . " (Dept: " . $user->department_id . ")\n";
}

echo "\n=== Department Summary ===\n";
$departments = App\Models\Department::all();
foreach($departments as $dept) {
    $totalFaculty = App\Models\User::where('department_id', $dept->id)
        ->whereHas('role', function($q) {
            $q->where('name', 'Faculty Member');
        })->count();
    
    $partTime = App\Models\User::where('department_id', $dept->id)
        ->where('faculty_type', 'part-time')
        ->whereHas('role', function($q) {
            $q->where('name', 'Faculty Member');
        })->count();
    
    $fullTime = App\Models\User::where('department_id', $dept->id)
        ->where('faculty_type', 'full-time')
        ->whereHas('role', function($q) {
            $q->where('name', 'Faculty Member');
        })->count();
    
    echo $dept->name . ": Total=" . $totalFaculty . ", Part-time=" . $partTime . ", Full-time=" . $fullTime . "\n";
}
