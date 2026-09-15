<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->text('offer')->nullable()->after('evidence_ids');
            $table->text('next_step')->nullable()->after('offer');
            $table->string('next_step_url', 2048)->nullable()->after('next_step');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            $table->dropColumn(['offer', 'next_step', 'next_step_url']);
        });
    }
};
