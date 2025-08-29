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
        Schema::create('compliance_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_document_id')->constrained()->cascadeOnDelete();
            $table->string('drive_link');
            $table->string('description')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });

        // Add index for faster queries
        Schema::table('compliance_links', function (Blueprint $table) {
            $table->index(['compliance_document_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_links');
    }
};
