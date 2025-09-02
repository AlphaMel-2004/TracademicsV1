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

        // Note: compliance_documents table creation moved to dedicated migration
        // to avoid conflicts with table structure updates
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};


