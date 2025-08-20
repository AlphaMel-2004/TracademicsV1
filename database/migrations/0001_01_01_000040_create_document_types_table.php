<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('faculty_assignments')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->string('self_evaluation')->nullable();
            $table->enum('status', ['Compiled','Not Compiled','Not Applicable'])->default('Not Compiled');
            $table->string('drive_link')->nullable();
            $table->unique(['assignment_id','document_type_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_documents');
        Schema::dropIfExists('document_types');
    }
};


