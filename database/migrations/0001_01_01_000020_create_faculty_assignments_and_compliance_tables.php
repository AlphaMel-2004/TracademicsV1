<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->unique(['faculty_id', 'subject_id', 'term_id']);
            $table->timestamps();
        });

        Schema::create('compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('faculty_assignments')->cascadeOnDelete();
            $table->enum('status', ['Compiled', 'Not Compiled', 'Not Applicable'])->default('Not Compiled');
            $table->string('drive_link')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('compliances');
        Schema::dropIfExists('faculty_assignments');
    }
};



