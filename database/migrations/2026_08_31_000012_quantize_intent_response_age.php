<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intent_responses', function (Blueprint $table) {
            $table->string('age_group', 32)->nullable()->after('angle_id');
        });

        foreach (config('capture.age_groups', []) as $value => $group) {
            $query = DB::table('intent_responses')->whereNull('age_group');

            if (is_int($group['max'] ?? null)) {
                $query->whereBetween('age', [$group['min'], $group['max']]);
            } else {
                $query->where('age', '>=', $group['min']);
            }

            $query->update(['age_group' => $value]);
        }

        Schema::table('intent_responses', function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }

    public function down(): void
    {
        Schema::table('intent_responses', function (Blueprint $table) {
            $table->unsignedSmallInteger('age')->after('age_group');
        });

        Schema::table('intent_responses', function (Blueprint $table) {
            $table->dropColumn('age_group');
        });
    }
};
