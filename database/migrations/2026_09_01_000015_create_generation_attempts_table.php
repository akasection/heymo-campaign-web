<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('provider', 64);
            $table->string('model', 128);
            $table->string('prompt_version', 64);
            $table->json('prompt_payload');
            $table->json('raw_response');
            $table->json('violations')->nullable();
            $table->string('status', 32);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_attempts');
    }
};
