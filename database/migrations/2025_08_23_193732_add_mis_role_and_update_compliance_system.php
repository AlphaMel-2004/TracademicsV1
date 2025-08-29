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
        // Add MIS role to roles table
        DB::table('roles')->insertOrIgnore([
            'name' => 'MIS',
            'scope' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create user activity logs table for MIS monitoring
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // login, logout, create, update, delete, etc.
            $table->string('description');
            $table->json('metadata')->nullable(); // additional data
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // Update compliance documents to support multiple links
        Schema::create('compliance_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_id')->constrained()->cascadeOnDelete();
            $table->string('drive_link');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        // Update document types to categorize them
        Schema::table('document_types', function (Blueprint $table) {
            $table->enum('submission_type', ['once_per_semester', 'per_subject'])->default('per_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('submission_type');
        });
        
        Schema::dropIfExists('compliance_submissions');
        Schema::dropIfExists('user_activity_logs');
        
        DB::table('roles')->where('name', 'MIS')->delete();
    }
};
