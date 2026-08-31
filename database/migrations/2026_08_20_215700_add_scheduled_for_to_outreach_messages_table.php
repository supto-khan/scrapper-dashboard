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
        Schema::table('outreach_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('outreach_messages', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('staged_at');
                $table->index('scheduled_for');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outreach_messages', function (Blueprint $table) {
            if (Schema::hasColumn('outreach_messages', 'scheduled_for')) {
                $table->dropIndex(['scheduled_for']);
                $table->dropColumn('scheduled_for');
            }
        });
    }
};
