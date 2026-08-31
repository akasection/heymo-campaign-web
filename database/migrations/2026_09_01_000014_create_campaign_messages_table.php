<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence_position');
            $table->string('channel', 16)->default('email');
            $table->string('role', 16);
            $table->string('subject', 255);
            $table->string('headline', 255);
            $table->json('body_paragraphs');
            $table->json('evidence_ids');
            $table->string('status', 32)->default('generated');
            $table->timestamps();

            $table->unique(['campaign_id', 'sequence_position', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_messages');
    }
};
