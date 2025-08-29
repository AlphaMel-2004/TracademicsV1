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
        // First expand the enum to include both old and new values temporarily
        DB::statement("ALTER TABLE compliance_documents MODIFY COLUMN status ENUM('Compiled', 'Not Compiled', 'Complied', 'Not Complied', 'Not Applicable') DEFAULT 'Not Compiled'");
        
        // Now update existing data to use new terminology
        DB::table('compliance_documents')->where('status', 'Compiled')->update(['status' => 'Complied']);
        DB::table('compliance_documents')->where('status', 'Not Compiled')->update(['status' => 'Not Complied']);
        
        // Finally, restrict the enum to only the new values
        DB::statement("ALTER TABLE compliance_documents MODIFY COLUMN status ENUM('Complied', 'Not Complied', 'Not Applicable') DEFAULT 'Not Complied'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old terminology
        DB::table('compliance_documents')->where('status', 'Complied')->update(['status' => 'Compiled']);
        DB::table('compliance_documents')->where('status', 'Not Complied')->update(['status' => 'Not Compiled']);
        
        // Revert enum column
        DB::statement("ALTER TABLE compliance_documents MODIFY COLUMN status ENUM('Compiled', 'Not Compiled', 'Not Applicable') DEFAULT 'Not Compiled'");
    }
};
