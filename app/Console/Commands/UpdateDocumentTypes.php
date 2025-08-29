<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DocumentType;

class UpdateDocumentTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'document-types:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update document types with submission types and descriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating document types...');

        // Semester-wide requirements (submit once per semester)
        $semesterWideTypes = [
            'Information Sheet',
            'TOR/Diploma',
            'Certificates of Trainings',
            'Faculty Load'
        ];

        // Subject-specific requirements (submit for each subject)
        $subjectSpecificTypes = [
            'Syllabus',
            'Course Outline', 
            'Lesson Plans',
            'Class Record',
            'Assessment Tools',
            'Instructional Materials',
            'Student Evaluation',
            'Grading System'
        ];

        // Update semester-wide types
        foreach ($semesterWideTypes as $name) {
            $docType = DocumentType::where('name', $name)->first();
            if ($docType) {
                $docType->update([
                    'submission_type' => 'semester'
                ]);
                $this->line("✓ Updated: {$name} (semester-wide)");
            } else {
                DocumentType::create([
                    'name' => $name,
                    'submission_type' => 'semester'
                ]);
                $this->line("✓ Created: {$name} (semester-wide)");
            }
        }

        // Update subject-specific types
        foreach ($subjectSpecificTypes as $name) {
            $docType = DocumentType::where('name', $name)->first();
            if ($docType) {
                $docType->update([
                    'submission_type' => 'subject'
                ]);
                $this->line("✓ Updated: {$name} (subject-specific)");
            } else {
                DocumentType::create([
                    'name' => $name,
                    'submission_type' => 'subject'
                ]);
                $this->line("✓ Created: {$name} (subject-specific)");
            }
        }

        $this->info('Document types updated successfully!');
        
        // Display summary
        $semesterCount = DocumentType::semesterWide()->count();
        $subjectCount = DocumentType::subjectSpecific()->count();
        
        $this->table(['Type', 'Count'], [
            ['Semester-wide', $semesterCount],
            ['Subject-specific', $subjectCount],
            ['Total', $semesterCount + $subjectCount]
        ]);

        return 0;
    }
}
