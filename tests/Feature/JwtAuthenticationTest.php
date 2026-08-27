<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class JwtAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret' => 'test-jwt-secret-that-is-long-enough',
            'jwt.issuer' => 'http://localhost',
            'jwt.audience' => 'heymo-campaign-web',
        ]);
    }

    public function test_api_routes_require_a_bearer_jwt(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_a_revoked_jwt_can_no_longer_access_api_data(): void
    {
        [$token, $user] = $this->authenticateUser();

        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/user')->assertOk();
        $this->assertDatabaseHas('auth_sessions', ['user_id' => $user->id, 'revoked_at' => null]);

        $this->postAuthJson('/api/auth/logout', [], ['Authorization' => 'Bearer '.$token])->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/user')->assertUnauthorized();
        $this->assertDatabaseMissing('auth_sessions', ['user_id' => $user->id, 'revoked_at' => null]);
    }

    public function test_malformed_jwts_are_rejected(): void
    {
        [$token] = $this->authenticateUser();

        $this->withHeader('Authorization', 'Bearer '.$token.'invalid')
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_a_web_session_can_issue_a_fresh_frontend_jwt(): void
    {
        Mail::fake();
        [$token, $user] = $this->authenticateUser();

        $this->postAuthJson('/api/auth/token')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['access_token', 'expires_in']);

        $this->assertNotEmpty($token);
    }

    private function authenticateUser(): array
    {
        Mail::fake();
        $organization = Organization::query()->create([
            'name' => 'Test Organization',
            'slug' => 'test-'.Str::lower(Str::random(8)),
        ]);
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role' => User::ROLE_ADMIN,
        ]);

        $challengeId = $this->postAuthJson('/api/auth/request-code', ['email' => $user->email])->json('challenge_id');
        $mail = Mail::sent(LoginOtpMail::class)->firstOrFail();
        $response = $this->postAuthJson('/api/auth/verify-code', [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => $mail->code,
        ])->assertOk();

        return [$response->json('access_token'), $user];
    }

    private function postAuthJson(string $uri, array $payload = [], array $headers = [])
    {
        $csrfToken = Str::random(40);

        return $this->withSession(['_token' => $csrfToken])
            ->withHeaders(array_merge(['X-CSRF-TOKEN' => $csrfToken], $headers))
            ->postJson($uri, $payload);
    }
}
