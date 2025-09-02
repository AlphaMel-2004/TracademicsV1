<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\FacultyAssignment;
use App\Models\ComplianceDocument;
use App\Models\ComplianceLink;

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
                
                // Use firstOrCreate to avoid duplicate assignments
                $assignment = FacultyAssignment::firstOrCreate(
                    [
                        'faculty_id' => $facultyMember->id,
                        'subject_id' => $subject->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'program_id' => $facultyMember->program_id,
                        'status' => 'Active',
                    ]
                );

                // Only create compliance documents if this is a new assignment
                if ($assignment->wasRecentlyCreated) {
                    $this->createComplianceDocuments($assignment);
                }
            }
        }
    }

    private function createComplianceDocuments($assignment)
    {
        $documentTypes = DB::table('document_types')->get();
        
        foreach ($documentTypes as $docType) {
            // Randomly set some documents as complied, some as not complied
            $status = rand(1, 3) === 1 ? 'Complied' : 'Not Complied';
            
            // Create compliance document with new structure
            $complianceDoc = ComplianceDocument::create([
                'user_id' => $assignment->faculty_id,
                'document_type_id' => $docType->id,
                'term_id' => $assignment->semester_id, // Note: semester_id maps to term_id in compliance_documents
                'subject_id' => $assignment->subject_id,
                'status' => $status,
                'remarks' => $status === 'Complied' ? 'Document submitted successfully' : null,
            ]);
            
            // If the document is complied, create a compliance link
            if ($status === 'Complied') {
                ComplianceLink::create([
                    'compliance_document_id' => $complianceDoc->id,
                    'drive_link' => 'https://drive.google.com/file/d/sample' . rand(1000, 9999) . '/view',
                    'description' => 'Document submission for ' . $docType->name,
                    'submitted_by' => $assignment->faculty_id,
                    'submitted_at' => now(),
                ]);
            }
        }
    }
}
