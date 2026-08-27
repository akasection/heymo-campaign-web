<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organizations = collect([
            ['name' => 'Heymo Org', 'slug' => 'heymo-org'],
            ['name' => 'Lexical Labs', 'slug' => 'lexical-labs'],
            ['name' => 'EJE Science', 'slug' => 'eje-science'],
            ['name' => 'XO Health Group', 'slug' => 'xo-health-group'],
        ])->mapWithKeys(function (array $attributes) {
            $organization = Organization::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                ['name' => $attributes['name']],
            );

            return [$organization->slug => $organization];
        });

        $users = [
            [
                'name' => 'Mara Ellis',
                'email' => 'admin@heymo.test',
                'organization' => 'heymo-org',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Noah Williams',
                'email' => 'ops@lexical.test',
                'organization' => 'lexical-labs',
                'role' => User::ROLE_MEMBER,
            ],
            [
                'name' => 'Samira Patel',
                'email' => 'review@eje.test',
                'organization' => 'eje-science',
                'role' => User::ROLE_MEMBER,
            ],
            [
                'name' => 'Jonah Brooks',
                'email' => 'team@xohealth.test',
                'organization' => 'xo-health-group',
                'role' => User::ROLE_MEMBER,
            ],
        ];

        foreach ($users as $attributes) {
            User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                [
                    'name' => $attributes['name'],
                    'organization_id' => $organizations[$attributes['organization']]->id,
                    'role' => $attributes['role'],
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(64)),
                ],
            );
        }
    }
}
