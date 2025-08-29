<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing document types
        DB::table('document_types')->delete();

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

        $now = now();

        // Insert semester-wide types
        foreach ($semesterWideTypes as $name) {
            DB::table('document_types')->insert([
                'name' => $name,
                'submission_type' => 'semester',
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // Insert subject-specific types
        foreach ($subjectSpecificTypes as $name) {
            DB::table('document_types')->insert([
                'name' => $name,
                'submission_type' => 'subject',
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        echo "Document types seeded successfully!\n";
        echo "Semester-wide: " . count($semesterWideTypes) . " types\n";
        echo "Subject-specific: " . count($subjectSpecificTypes) . " types\n";
    }
}
