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
        Schema::table('faculty_assignments', function (Blueprint $table) {
            // Check if the old unique constraint exists and drop it
            try {
                $table->dropUnique(['faculty_id', 'subject_id', 'term_id']);
            } catch (Exception $e) {
                // Constraint may not exist, ignore the error
            }
            
            // Add the correct unique constraint for the new structure
            if (Schema::hasColumn('faculty_assignments', 'semester_id')) {
                try {
                    $table->unique(['faculty_id', 'subject_id', 'semester_id'], 'faculty_assignments_unique');
                } catch (Exception $e) {
                    // Constraint may already exist, ignore the error
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_assignments', function (Blueprint $table) {
            try {
                $table->dropUnique('faculty_assignments_unique');
            } catch (Exception $e) {
                // Constraint may not exist, ignore the error
            }
        });
    }
};
