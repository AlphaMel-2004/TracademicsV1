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
        Schema::create('semester_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('logged_in_at');
            $table->timestamp('logged_out_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add current_semester_id to users table for session tracking
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_semester_id']);
            $table->dropColumn('current_semester_id');
        });
        
        Schema::dropIfExists('semester_sessions');
    }
};
