<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('angles', function (Blueprint $table) {
            $table->string('next_step_url', 2048)->nullable()->after('next_step');
        });
    }

    public function down(): void
    {
        Schema::table('angles', function (Blueprint $table) {
            $table->dropColumn('next_step_url');
        });
    }
};
