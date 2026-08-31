<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors');
            $table->string('channel', 32);
            $table->string('email', 255);
            $table->string('source', 120);
            $table->string('policy_version', 64);
            $table->timestamp('consented_at');
            $table->timestamps();

            $table->index(['visitor_id', 'channel', 'consented_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
