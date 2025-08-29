<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get faculty users
        $faculty = User::where('role_id', 4)->get(); // Faculty Member role
        $subjects = Subject::all();
        $semesters = Semester::all();

        if ($faculty->isEmpty() || $subjects->isEmpty() || $semesters->isEmpty()) {
            return;
        }

        // Create some faculty assignments
        foreach ($faculty as $facultyMember) {
            // Assign 1-2 subjects per faculty
            $numAssignments = rand(1, 2);
            $selectedSubjects = $subjects->random($numAssignments);
            
            foreach ($selectedSubjects as $subject) {
                $semester = $semesters->random();
                
                $assignment = FacultyAssignment::create([
                    'faculty_id' => $facultyMember->id,
                    'subject_id' => $subject->id,
                    'semester_id' => $semester->id,
                    'program_id' => $facultyMember->program_id,
                ]);

                // Create compliance documents for this assignment
                $this->createComplianceDocuments($assignment);
            }
        }
    }

    private function createComplianceDocuments($assignment)
    {
        $documentTypes = DB::table('document_types')->get();
        
        foreach ($documentTypes as $docType) {
            // Randomly set some documents as complied, some as not complied
            $status = rand(1, 3) === 1 ? 'Complied' : 'Not Complied';
            $driveLink = $status === 'Complied' ? 'https://drive.google.com/file/d/sample' . rand(1000, 9999) . '/view' : null;
            
            ComplianceDocument::create([
                'assignment_id' => $assignment->id,
                'document_type_id' => $docType->id,
                'status' => $status,
                'drive_link' => $driveLink,
            ]);
        }
    }
}
