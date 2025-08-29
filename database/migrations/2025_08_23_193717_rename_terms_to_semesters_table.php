<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the terms table to semesters
        Schema::rename('terms', 'semesters');
        
        // Update foreign key references in other tables
        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->renameColumn('term_id', 'semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the foreign key changes
        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->renameColumn('semester_id', 'term_id');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
        });
        
        // Rename back to terms
        Schema::rename('semesters', 'terms');
    }
};
