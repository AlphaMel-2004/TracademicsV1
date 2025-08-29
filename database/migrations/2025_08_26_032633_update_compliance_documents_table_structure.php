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
        // First, clear existing data to avoid foreign key constraint issues
        DB::table('compliance_documents')->delete();
        
        Schema::table('compliance_documents', function (Blueprint $table) {
            // Add user_id column if it doesn't exist
            if (!Schema::hasColumn('compliance_documents', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            }
            
            // Add term_id column if it doesn't exist
            if (!Schema::hasColumn('compliance_documents', 'term_id')) {
                $table->foreignId('term_id')->after('document_type_id')->constrained('terms')->cascadeOnDelete();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compliance_documents', function (Blueprint $table) {
            if (Schema::hasColumn('compliance_documents', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('compliance_documents', 'term_id')) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            }
            
            if (Schema::hasColumn('compliance_documents', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }
            
            if (Schema::hasColumn('compliance_documents', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
