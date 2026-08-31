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
        if (Schema::hasTable('outreach_messages')) {
            // Drop foreign key constraints if present, modify column to nullable, and re-add
            try {
                DB::statement("ALTER TABLE outreach_messages MODIFY COLUMN company_id BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {
                // If constrained by foreign key
                $fks = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'outreach_messages' AND COLUMN_NAME = 'company_id' AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($fks as $fk) {
                    DB::statement("ALTER TABLE outreach_messages DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                }
                DB::statement("ALTER TABLE outreach_messages MODIFY COLUMN company_id BIGINT UNSIGNED NULL");
                try {
                    DB::statement("ALTER TABLE outreach_messages ADD CONSTRAINT fk_outreach_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL");
                } catch (\Throwable $e2) {}
            }

            try {
                DB::statement("ALTER TABLE outreach_messages MODIFY COLUMN contact_id BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {
                $fks = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'outreach_messages' AND COLUMN_NAME = 'contact_id' AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($fks as $fk) {
                    DB::statement("ALTER TABLE outreach_messages DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                }
                DB::statement("ALTER TABLE outreach_messages MODIFY COLUMN contact_id BIGINT UNSIGNED NULL");
                try {
                    DB::statement("ALTER TABLE outreach_messages ADD CONSTRAINT fk_outreach_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL");
                } catch (\Throwable $e2) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep nullable
    }
};
