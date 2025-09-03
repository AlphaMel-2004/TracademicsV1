<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CoursesSeeder extends Seeder
{
    public function run(): void
    {
        // First ensure departments exist
        $asbme = Department::firstOrCreate(['name' => 'ASBME Department']);
        
        // Create courses
        $courses = [
            ['course_code' => 'BSIT', 'course_name' => 'Bachelor of Science in Information Technology', 'department_id' => $asbme->id],
            ['course_code' => 'BSBA', 'course_name' => 'Bachelor of Science in Business Administration', 'department_id' => $asbme->id],
            ['course_code' => 'BSPSY', 'course_name' => 'Bachelor of Science in Psychology', 'department_id' => $asbme->id],
        ];

        foreach ($courses as $course) {
            Course::firstOrCreate(
                ['course_code' => $course['course_code']], 
                $course
            );
        }

        // Update document types with submission types
        $oncePerSemesterDocs = [
            'Information Sheet',
            'TOR/Diploma', 
            'Certificates of Trainings (past 5 years)',
            'Faculty Load'
        ];

        $perSubjectDocs = [
            'Class Record',
            'Course Syllabus',
            'Lesson Plan',
            'Teaching Materials',
            'Assessment Tools',
            'Student Evaluation',
            'Midterm Grades',
            'Final Grades'
        ];

        DB::table('document_types')
            ->whereIn('name', $oncePerSemesterDocs)
            ->update(['submission_type' => 'semester']);

        DB::table('document_types')
            ->whereIn('name', $perSubjectDocs)
            ->update(['submission_type' => 'subject']);
    }
}
