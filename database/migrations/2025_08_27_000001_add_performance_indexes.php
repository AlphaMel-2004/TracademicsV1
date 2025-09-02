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
        Schema::table('users', function (Blueprint $table) {
            // Index for role-based queries
            $table->index(['role_id', 'department_id'], 'users_role_department_idx');
            $table->index(['role_id', 'program_id'], 'users_role_program_idx');
            $table->index(['department_id', 'faculty_type'], 'users_dept_faculty_type_idx');
            $table->index(['program_id', 'faculty_type'], 'users_prog_faculty_type_idx');
        });

        Schema::table('compliance_documents', function (Blueprint $table) {
            // Index for status and user queries (updated for new table structure)
            $table->index(['status', 'user_id'], 'compliance_status_user_idx');
            $table->index(['user_id', 'status'], 'compliance_user_status_idx');
            $table->index(['document_type_id', 'status'], 'compliance_doctype_status_idx');
            $table->index(['term_id', 'status'], 'compliance_term_status_idx');
            $table->index(['subject_id', 'status'], 'compliance_subject_status_idx');
        });

        Schema::table('faculty_assignments', function (Blueprint $table) {
            // Index for faculty and semester queries (using semester_id after table rename)
            $table->index(['faculty_id', 'semester_id'], 'faculty_assignments_faculty_semester_idx');
            $table->index(['subject_id', 'semester_id'], 'faculty_assignments_subject_semester_idx');
            $table->index(['program_id', 'semester_id'], 'faculty_assignments_program_semester_idx');
        });

        Schema::table('compliance_links', function (Blueprint $table) {
            // Index for compliance document queries
            $table->index(['compliance_document_id', 'submitted_at'], 'compliance_links_doc_submitted_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_department_idx');
            $table->dropIndex('users_role_program_idx');
            $table->dropIndex('users_dept_faculty_type_idx');
            $table->dropIndex('users_prog_faculty_type_idx');
        });

        Schema::table('compliance_documents', function (Blueprint $table) {
            $table->dropIndex('compliance_status_user_idx');
            $table->dropIndex('compliance_user_status_idx');
            $table->dropIndex('compliance_doctype_status_idx');
            $table->dropIndex('compliance_term_status_idx');
            $table->dropIndex('compliance_subject_status_idx');
        });

        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropIndex('faculty_assignments_faculty_semester_idx');
            $table->dropIndex('faculty_assignments_subject_semester_idx');
            $table->dropIndex('faculty_assignments_program_semester_idx');
        });

        Schema::table('compliance_links', function (Blueprint $table) {
            $table->dropIndex('compliance_links_doc_submitted_idx');
        });
    }
};
