<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\LoginChallenge;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordlessLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.secret' => 'test-jwt-secret-that-is-long-enough',
            'jwt.issuer' => 'http://localhost',
            'jwt.audience' => 'heymo-campaign-web',
            'otp.log_codes' => false,
        ]);
    }

    public function test_requesting_a_code_sends_a_mail_and_stores_only_a_hash(): void
    {
        Mail::fake();
        $user = $this->makeUser(User::ROLE_ADMIN, 'admin@example.test');

        $response = $this->postAuthJson('/api/auth/request-code', ['email' => $user->email]);

        $response->assertAccepted()->assertJsonStructure(['challenge_id', 'expires_in', 'retry_after']);
        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use ($user) {
            return $mail->user->is($user) && preg_match('/^\d{3}-\d{3}$/', $mail->code) === 1;
        });

        $challenge = LoginChallenge::query()->firstOrFail();
        $mail = Mail::sent(LoginOtpMail::class)->first();

        $this->assertNotNull($mail);
        $this->assertTrue(Hash::check(str_replace('-', '', $mail->code), $challenge->code_hash));
        $this->assertNotSame($mail->code, $challenge->code_hash);
    }

    public function test_unknown_emails_receive_a_neutral_response_without_a_mail(): void
    {
        Mail::fake();

        $response = $this->postAuthJson('/api/auth/request-code', ['email' => 'unknown@example.test']);

        $response->assertAccepted()->assertJsonStructure(['challenge_id', 'expires_in', 'retry_after']);
        Mail::assertNothingSent();
        $this->assertDatabaseCount('login_challenges', 0);
    }

    public function test_a_valid_code_authenticates_the_web_session_and_returns_a_jwt(): void
    {
        Mail::fake();
        $user = $this->makeUser(User::ROLE_ADMIN, 'admin@example.test');
        $challengeId = $this->requestChallenge($user);
        $mail = Mail::sent(LoginOtpMail::class)->firstOrFail();

        $response = $this->postAuthJson('/api/auth/verify-code', [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => $mail->code,
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', User::ROLE_ADMIN)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['organization'],
            ]);
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertDatabaseHas('auth_sessions', ['user_id' => $user->id, 'revoked_at' => null]);

        $token = $response->json('access_token');
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_a_code_can_be_entered_with_or_without_the_dash_and_cannot_be_replayed(): void
    {
        Mail::fake();
        $user = $this->makeUser(User::ROLE_MEMBER, 'member@example.test');
        $challengeId = $this->requestChallenge($user);
        $mail = Mail::sent(LoginOtpMail::class)->firstOrFail();

        $response = $this->postAuthJson('/api/auth/verify-code', [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => str_replace('-', '', $mail->code),
        ]);

        $response->assertOk();

        $this->postAuthJson('/api/auth/verify-code', [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => $mail->code,
        ])->assertStatus(410)->assertJsonPath('reason', 'used');
    }

    public function test_expired_code_is_rejected(): void
    {
        Mail::fake();
        $user = $this->makeUser(User::ROLE_MEMBER, 'member@example.test');
        $challengeId = $this->requestChallenge($user);
        $mail = Mail::sent(LoginOtpMail::class)->firstOrFail();

        LoginChallenge::query()->where('public_id', $challengeId)->update(['expires_at' => now()->subMinute()]);

        $this->postAuthJson('/api/auth/verify-code', [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => $mail->code,
        ])->assertStatus(410)->assertJsonPath('reason', 'expired');
    }

    public function test_repeated_invalid_codes_lock_the_challenge(): void
    {
        Mail::fake();
        config(['otp.max_attempts' => 2]);
        $user = $this->makeUser(User::ROLE_MEMBER, 'member@example.test');
        $challengeId = $this->requestChallenge($user);

        $payload = [
            'challenge_id' => $challengeId,
            'email' => $user->email,
            'code' => '000-000',
        ];

        $this->postAuthJson('/api/auth/verify-code', $payload)->assertStatus(422);
        $this->postAuthJson('/api/auth/verify-code', $payload)->assertStatus(429)->assertJsonPath('reason', 'locked');
        $this->assertDatabaseHas('login_challenges', ['public_id' => $challengeId, 'attempts' => 2]);
    }

    private function requestChallenge(User $user): string
    {
        return $this->postAuthJson('/api/auth/request-code', ['email' => $user->email])->json('challenge_id');
    }

    private function postAuthJson(string $uri, array $payload = [])
    {
        $csrfToken = Str::random(40);

        return $this->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->postJson($uri, $payload);
    }

    private function makeUser(string $role, string $email): User
    {
        $organization = Organization::query()->create([
            'name' => 'Test Organization',
            'slug' => 'test-'.Str::lower(Str::random(8)),
        ]);

        return User::factory()->create([
            'email' => $email,
            'organization_id' => $organization->id,
            'role' => $role,
        ]);
    }
}
