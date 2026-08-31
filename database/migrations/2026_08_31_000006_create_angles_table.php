<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands');
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->text('audience');
            $table->text('trigger_moment');
            $table->text('primary_job');
            $table->text('tension');
            $table->text('desired_outcome');
            $table->text('single_promise');
            $table->text('proof');
            $table->text('objection');
            $table->text('offer');
            $table->string('tone', 32);
            $table->text('next_step');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['brand_id', 'slug']);
            $table->index(['brand_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angles');
    }
};
