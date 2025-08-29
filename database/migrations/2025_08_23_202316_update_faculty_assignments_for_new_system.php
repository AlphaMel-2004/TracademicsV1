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
            // Add new fields for the updated system
            $table->string('subject_code')->nullable()->after('subject_id');
            $table->string('subject_description')->nullable()->after('subject_code');
            $table->string('status')->default('Active')->after('program_id');
            
            // Make subject_id nullable since we might use subject_code instead
            $table->unsignedBigInteger('subject_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropColumn(['subject_code', 'subject_description', 'status']);
            $table->unsignedBigInteger('subject_id')->nullable(false)->change();
        });
    }
};
