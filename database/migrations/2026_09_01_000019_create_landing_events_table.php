<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_events', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 36)->index();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('landing_identifier', 80);
            $table->foreignId('angle_id')->nullable()->constrained('angles')->nullOnDelete();
            $table->json('attribution')->nullable();
            $table->json('device')->nullable();
            $table->timestamp('landed_at');
            $table->timestamps();

            $table->index(['brand_id', 'landed_at']);
            $table->index(['angle_id', 'landed_at']);
            $table->index(['landing_identifier', 'landed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_events');
    }
};
