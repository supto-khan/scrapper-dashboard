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
            if (!Schema::hasColumn('outreach_messages', 'step')) {
                $table->unsignedTinyInteger('step')->default(1)->after('id');
            }
            if (!Schema::hasColumn('outreach_messages', 'direction')) {
                $table->string('direction', 20)->default('outbound')->after('channel');
            }
            if (!Schema::hasColumn('outreach_messages', 'sender_email')) {
                $table->string('sender_email', 255)->nullable()->after('direction');
            }
            if (!Schema::hasColumn('outreach_messages', 'message_id')) {
                $table->string('message_id', 255)->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('outreach_messages', 'in_reply_to')) {
                $table->string('in_reply_to', 255)->nullable()->after('message_id')->index();
            }
            if (!Schema::hasColumn('outreach_messages', 'opportunity_id')) {
                $table->unsignedBigInteger('opportunity_id')->nullable()->after('contact_id')->index();
            }
            if (!Schema::hasColumn('outreach_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('opened_at');
            }
            if (!Schema::hasColumn('outreach_messages', 'generator_type')) {
                $table->string('generator_type', 50)->default('template_engine')->after('segment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outreach_messages', function (Blueprint $table) {
            $table->dropColumn([
                'step',
                'direction',
                'sender_email',
                'message_id',
                'in_reply_to',
                'opportunity_id',
                'read_at',
                'generator_type',
            ]);
        });
    }
};
