<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->string('channel', 32)->default('email');
            $table->string('reason', 64);
            $table->string('source', 120);
            $table->timestamp('suppressed_at');
            $table->timestamps();

            $table->index(['visitor_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppressions');
    }
};
