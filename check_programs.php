<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Program and Department Information:\n";
echo "==================================\n\n";

$programs = App\Models\Program::with('department')->get();

foreach ($programs as $program) {
    $facultyCount = App\Models\User::where('program_id', $program->id)
        ->whereHas('role', function($q) { 
            $q->where('name', 'Faculty Member'); 
        })->count();
    
    echo "Program: " . $program->name . " (ID: " . $program->id . ")\n";
    echo "  Department: " . $program->department->name . " (ID: " . $program->department->id . ")\n";
    echo "  Faculty Count: " . $facultyCount . "\n\n";
}

echo "Navigation Path for Programs with Faculty:\n";
echo "=========================================\n";
echo "To see faculty, navigate to:\n";
echo "1. Department: Computer Studies (ID: 1) → Program: BSIT (ID: 1) → 2 faculty members\n";
echo "2. Department: Arts and Sciences (ID: 2) → Program: BSPsych (ID: 2) → 1 faculty member\n";
echo "3. Department: Nursing (ID: 3) → Program: BS Nursing (ID: 9) → 1 faculty member\n";
