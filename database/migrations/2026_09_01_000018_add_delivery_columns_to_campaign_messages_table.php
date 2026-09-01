<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'sent_at']);
        });
    }
};
