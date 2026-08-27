<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_page_redirects_guests_to_passwordless_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_authenticated_members_can_view_the_backoffice(): void
    {
        $user = $this->makeUser(User::ROLE_MEMBER);

        $this->actingAs($user, 'web')->get('/admin')->assertOk();
    }

    public function test_only_admins_receive_backoffice_management_capability(): void
    {
        $member = $this->makeUser(User::ROLE_MEMBER);
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->assertFalse($member->canManageBackoffice());
        $this->assertNotContains('backoffice.manage', $member->capabilities());
        $this->assertTrue($admin->canManageBackoffice());
        $this->assertContains('backoffice.manage', $admin->capabilities());
    }

    private function makeUser(string $role): User
    {
        $organization = Organization::query()->create([
            'name' => 'Test Organization',
            'slug' => 'test-'.Str::lower(Str::random(8)),
        ]);

        return User::factory()->create([
            'organization_id' => $organization->id,
            'role' => $role,
        ]);
    }
}
