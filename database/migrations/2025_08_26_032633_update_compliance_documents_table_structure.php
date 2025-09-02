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
        // Create compliance_documents table with the correct structure
        if (!Schema::hasTable('compliance_documents')) {
            Schema::create('compliance_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
                $table->foreignId('term_id')->constrained('semesters')->cascadeOnDelete(); // Note: references semesters, not terms
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete();
                $table->enum('status', ['Compiled', 'Not Compiled', 'Not Applicable'])->default('Not Compiled');
                $table->text('remarks')->nullable();
                $table->timestamps();
                
                // Ensure unique constraint for user, document type, term, and subject combination
                $table->unique(['user_id', 'document_type_id', 'term_id', 'subject_id'], 'compliance_unique');
            });
        } else {
            // If table exists, update its structure (this was the original intention)
            // First, clear existing data to avoid foreign key constraint issues
            DB::table('compliance_documents')->delete();
            
            Schema::table('compliance_documents', function (Blueprint $table) {
                // Add user_id column if it doesn't exist
                if (!Schema::hasColumn('compliance_documents', 'user_id')) {
                    $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
                }
                
                // Add term_id column if it doesn't exist
                if (!Schema::hasColumn('compliance_documents', 'term_id')) {
                    $table->foreignId('term_id')->after('document_type_id')->constrained('semesters')->cascadeOnDelete();
                }
                
                // Add subject_id column if it doesn't exist
                if (!Schema::hasColumn('compliance_documents', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->after('term_id')->constrained('subjects')->cascadeOnDelete();
                }
                
                // Remove assignment_id column if it exists (old structure)
                if (Schema::hasColumn('compliance_documents', 'assignment_id')) {
                    $table->dropForeign(['assignment_id']);
                    $table->dropColumn('assignment_id');
                }
                
                // Remove self_evaluation column if it exists (old structure)
                if (Schema::hasColumn('compliance_documents', 'self_evaluation')) {
                    $table->dropColumn('self_evaluation');
                }
                
                // Remove drive_link column if it exists (now handled by ComplianceLink)
                if (Schema::hasColumn('compliance_documents', 'drive_link')) {
                    $table->dropColumn('drive_link');
                }
                
                // Add remarks column if it doesn't exist
                if (!Schema::hasColumn('compliance_documents', 'remarks')) {
                    $table->text('remarks')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_documents');
    }
};
