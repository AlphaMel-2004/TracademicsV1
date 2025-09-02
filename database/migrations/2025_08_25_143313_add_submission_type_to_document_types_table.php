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
        Schema::table('document_types', function (Blueprint $table) {
            // Check if submission_type column exists and modify it, otherwise add it
            if (Schema::hasColumn('document_types', 'submission_type')) {
                // Modify existing column to use new enum values
                $table->enum('submission_type', ['semester', 'subject'])->default('subject')->change();
            } else {
                // Add new column if it doesn't exist
                $table->enum('submission_type', ['semester', 'subject'])->default('subject')->after('name');
            }
            
            // Add description column if it doesn't exist
            if (!Schema::hasColumn('document_types', 'description')) {
                $table->text('description')->nullable()->after('submission_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            // Only drop description column, keep submission_type as it was added in earlier migration
            if (Schema::hasColumn('document_types', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
