<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'latest_score_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedBigInteger('latest_score_id')->nullable()->after('website_url')->index('idx_companies_latest_score_id');
            });
        }

        // Backfill existing latest_score_id from scores table
        DB::statement("
            UPDATE companies c 
            INNER JOIN (
                SELECT company_id, MAX(id) AS max_id 
                FROM scores 
                GROUP BY company_id
            ) s ON c.id = s.company_id 
            SET c.latest_score_id = s.max_id
            WHERE c.latest_score_id IS NULL
        ");

        // Keep latest_score_id automatically synchronized on insert/delete
        DB::unprepared("DROP TRIGGER IF EXISTS trg_scores_after_insert");
        DB::unprepared("
            CREATE TRIGGER trg_scores_after_insert 
            AFTER INSERT ON scores 
            FOR EACH ROW 
            BEGIN 
                UPDATE companies SET latest_score_id = NEW.id WHERE id = NEW.company_id; 
            END
        ");

        DB::unprepared("DROP TRIGGER IF EXISTS trg_scores_after_delete");
        DB::unprepared("
            CREATE TRIGGER trg_scores_after_delete 
            AFTER DELETE ON scores 
            FOR EACH ROW 
            BEGIN 
                UPDATE companies 
                SET latest_score_id = (SELECT MAX(id) FROM scores WHERE company_id = OLD.company_id) 
                WHERE id = OLD.company_id; 
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_scores_after_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_scores_after_delete");

        if (Schema::hasColumn('companies', 'latest_score_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropIndex('idx_companies_latest_score_id');
                $table->dropColumn('latest_score_id');
            });
        }
    }
};
