<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intent_responses', function (Blueprint $table) {
            $table->string('fingerprint', 36)->nullable()->index();
            $table->unsignedInteger('session_duration_seconds')->nullable();
            $table->json('attribution')->nullable();
            $table->json('device')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('intent_responses', function (Blueprint $table) {
            $table->dropColumn(['fingerprint', 'session_duration_seconds', 'attribution', 'device']);
        });
    }
};
