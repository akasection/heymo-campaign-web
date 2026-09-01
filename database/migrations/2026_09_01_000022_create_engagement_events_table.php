<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_message_id')->constrained('campaign_messages')->cascadeOnDelete();
            $table->string('type', 16);
            $table->timestamp('occurred_at');
            $table->string('user_agent', 512)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['campaign_message_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_events');
    }
};
