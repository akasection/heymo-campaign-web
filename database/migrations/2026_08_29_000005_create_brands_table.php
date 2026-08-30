<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('tone_preset', 32)->default('balanced');
            $table->string('flow_preset', 32)->default('balanced');
            $table->string('tense_preset', 32)->default('balanced');
            $table->string('reading_level_preset', 32)->default('balanced');
            $table->json('preferred_terms');
            $table->json('avoided_terms');
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('heading_font', 64);
            $table->string('body_font', 64);
            $table->string('logo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
