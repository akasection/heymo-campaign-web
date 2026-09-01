<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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

        $heymoOrganization = $organizations['heymo-org'];
        $brandLogoFixtures = [
            'lexical-labs' => 'lexical-labs.png',
            'xo-health-group' => 'xo-health-group.png',
        ];

        foreach ([
            [
                'name' => 'Lexical Labs',
                'slug' => 'lexical-labs',
                'tone_preset' => 'informal',
                'flow_preset' => 'narrative',
                'tense_preset' => 'relaxed',
                'reading_level_preset' => 'simpler',
                'preferred_terms' => ['clear numbers', 'small steps', 'feel in the loop'],
                'avoided_terms' => ['miracle', 'perfect', 'guaranteed'],
                'primary_color' => '#2E5BFF',
                'secondary_color' => '#00B8A9',
                'heading_font' => 'space_grotesk',
                'body_font' => 'public_sans',
            ],
            [
                'name' => 'XO Health Group',
                'slug' => 'xo-health-group',
                'tone_preset' => 'formal',
                'flow_preset' => 'descriptive',
                'tense_preset' => 'serious',
                'reading_level_preset' => 'complex',
                'preferred_terms' => ['laboratory-reviewed', 'measured clarity', 'informed decisions'],
                'avoided_terms' => ['quick fix', 'hack', 'guaranteed'],
                'primary_color' => '#163A4A',
                'secondary_color' => '#7393A8',
                'heading_font' => 'source_serif',
                'body_font' => 'ibm_plex_sans',
            ],
        ] as $brandAttributes) {
            $brand = Brand::query()->updateOrCreate(
                [
                    'organization_id' => $heymoOrganization->id,
                    'slug' => $brandAttributes['slug'],
                ],
                $brandAttributes,
            );

            if (! array_key_exists($brand->slug, $brandLogoFixtures)) {
                throw new RuntimeException("Missing seeded brand logo mapping for slug: {$brand->slug}");
            }

            $this->seedBrandLogo($brand, $brandLogoFixtures[$brand->slug]);
        }

        $this->call(AngleSeeder::class);
        $this->call(DemoCampaignSeeder::class);
    }

    private function seedBrandLogo(Brand $brand, string $fixtureName): void
    {
        $sourcePath = database_path("seeders/assets/brands/logos/{$fixtureName}");

        if (! is_file($sourcePath)) {
            throw new RuntimeException("Missing seeded brand logo fixture: {$sourcePath}");
        }

        $destinationPath = "brands/{$brand->id}/{$fixtureName}";
        $disk = Storage::disk('public');
        $contents = file_get_contents($sourcePath);

        if ($contents === false || ! $disk->put($destinationPath, $contents)) {
            throw new RuntimeException("Could not copy seeded brand logo fixture: {$sourcePath}");
        }

        $oldPath = $brand->logo_path;

        try {
            $brand->forceFill(['logo_path' => $destinationPath])->save();
        } catch (\Throwable $exception) {
            if ($oldPath !== $destinationPath) {
                $disk->delete($destinationPath);
            }

            throw $exception;
        }

        if ($oldPath && $oldPath !== $destinationPath) {
            $disk->delete($oldPath);
        }
    }
}
