<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subject;
use App\Models\Term;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get faculty users
        $faculty = User::where('role_id', 4)->get(); // Faculty Member role
        $subjects = Subject::all();
        $terms = Term::all();

        if ($faculty->isEmpty() || $subjects->isEmpty() || $terms->isEmpty()) {
            return;
        }

        // Create some faculty assignments
        foreach ($faculty as $facultyMember) {
            // Assign 1-2 subjects per faculty
            $numAssignments = rand(1, 2);
            $selectedSubjects = $subjects->random($numAssignments);
            
            foreach ($selectedSubjects as $subject) {
                $term = $terms->random();
                
                $assignment = FacultyAssignment::create([
                    'faculty_id' => $facultyMember->id,
                    'subject_id' => $subject->id,
                    'term_id' => $term->id,
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
            // Randomly set some documents as compiled, some as not compiled
            $status = rand(1, 3) === 1 ? 'Compiled' : 'Not Compiled';
            $driveLink = $status === 'Compiled' ? 'https://drive.google.com/file/d/sample' . rand(1000, 9999) . '/view' : null;
            
            ComplianceDocument::create([
                'assignment_id' => $assignment->id,
                'document_type_id' => $docType->id,
                'status' => $status,
                'drive_link' => $driveLink,
            ]);
        }
    }
}
