<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors');
            $table->foreignId('angle_id')->constrained('angles');
            $table->foreignId('brand_id')->constrained('brands');
            $table->foreignId('intent_response_id')->constrained('intent_responses');
            $table->string('status', 32)->default('generating');
            $table->json('presentation_profile');
            $table->string('prompt_version', 64)->nullable();
            $table->timestamps();

            $table->index(['visitor_id', 'created_at']);
            $table->index(['angle_id', 'created_at']);
            $table->index(['brand_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
