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
        // Create courses table (replacing programs)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code'); // BSIT, BSBA, BSPSY
            $table->string('course_name');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Create curriculum table
        Schema::create('curriculums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "2022 Curriculum"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create curriculum subjects table
        Schema::create('curriculum_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curriculums')->cascadeOnDelete();
            $table->string('subject_code');
            $table->string('subject_description');
            $table->integer('year_level');
            $table->string('semester'); // 1st, 2nd
            $table->integer('units')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_subjects');
        Schema::dropIfExists('curriculums');
        Schema::dropIfExists('courses');
    }
};
