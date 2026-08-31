<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('angles', function (Blueprint $table) {
            $table->string('landing_identifier', 80)->nullable()->after('brand_id');
        });

        DB::statement(
            'CREATE UNIQUE INDEX angles_brand_landing_active_unique '
            .'ON angles (brand_id, landing_identifier) '
            .'WHERE landing_identifier IS NOT NULL AND deleted_at IS NULL',
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS angles_brand_landing_active_unique');

        Schema::table('angles', function (Blueprint $table) {
            $table->dropColumn('landing_identifier');
        });
    }
};
