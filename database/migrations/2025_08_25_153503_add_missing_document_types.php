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
        // First, let's clear existing document types to avoid duplicates
        DB::table('document_types')->delete();
        
        // Insert all required document types based on the official requirements
        $documentTypes = [
            ['name' => 'Information Sheet', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TOR/Diploma', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Certificates of Trainings Attended (past 5 years)', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Faculty Load', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Syllabus', 'created_at' => now(), 'updated_at' => now()], // Will be subject-specific
            ['name' => 'Prelim Test Questions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prelim Class Record', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Midterm Test Questions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Midterm Table of Specifications', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Midterm Class Record', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prefinal Test Questions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prefinal Class Record', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Final Test Questions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Final Table of Specifications', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Final Class Record', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Final Grading Sheet', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('document_types')->insert($documentTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('document_types')->delete();
    }
};
