<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add description column if it doesn't exist
        if (!Schema::hasColumn('document_types', 'description')) {
            Schema::table('document_types', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        // Update descriptions for existing document types
        $descriptions = [
            'Information Sheet' => 'Basic information about the faculty member including personal and professional details.',
            'TOR/Diploma' => 'Official transcript of records or diploma as proof of educational qualification.',
            'Certificates of Trainings Attended (past 5 years)' => 'Documentation of professional development activities and training programs attended in the last 5 years.',
            'Faculty Load' => 'Official assignment of teaching load and course responsibilities for the current semester.',
            'Syllabus' => 'Course outline including objectives, topics, assessment methods, and requirements for the specific subject.',
            'Prelim Test Questions' => 'Examination questions and materials for the preliminary examination period.',
            'Prelim Class Record' => 'Grade records and attendance tracking for students during the preliminary period.',
            'Midterm Test Questions' => 'Examination questions and materials for the midterm examination period.',
            'Midterm Table of Specifications' => 'Blueprint showing the distribution of test items according to learning objectives and cognitive levels for midterm exam.',
            'Midterm Class Record' => 'Grade records and attendance tracking for students during the midterm period.',
            'Prefinal Test Questions' => 'Examination questions and materials for the pre-final examination period.',
            'Prefinal Class Record' => 'Grade records and attendance tracking for students during the pre-final period.',
            'Final Test Questions' => 'Examination questions and materials for the final examination period.',
            'Final Table of Specifications' => 'Blueprint showing the distribution of test items according to learning objectives and cognitive levels for final exam.',
            'Final Class Record' => 'Grade records and attendance tracking for students during the final period.',
            'Final Grading Sheet' => 'Comprehensive summary of student grades and final computed ratings for the semester.',
        ];

        foreach ($descriptions as $name => $description) {
            DB::table('document_types')
                ->where('name', $name)
                ->update(['description' => $description]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('document_types', 'description')) {
            Schema::table('document_types', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
