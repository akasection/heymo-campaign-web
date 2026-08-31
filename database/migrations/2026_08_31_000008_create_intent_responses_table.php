<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intent_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors');
            $table->foreignId('angle_id')->constrained('angles');
            $table->string('landing_identifier', 80);
            $table->unsignedSmallInteger('age');
            $table->string('sex', 32);
            $table->string('sub_interest', 255);
            $table->string('trigger', 255);
            $table->text('concern');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['angle_id', 'captured_at']);
            $table->index(['visitor_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intent_responses');
    }
};
