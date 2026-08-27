<?php

namespace App\Services;

use App\Exceptions\LoginChallengeException;
use App\Mail\LoginOtpMail;
use App\Models\LoginChallenge;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class PasswordlessLoginService
{
    public function requestCode(string $email, ?string $ipAddress, ?string $userAgent): array
    {
        $email = self::normalizeEmail($email);
        $retryAfter = $this->rateLimitRetryAfter($email, $ipAddress);

        if ($retryAfter !== null) {
            return $this->neutralResponse($retryAfter);
        }

        $this->hitRateLimits($email, $ipAddress);
        $user = User::query()
            ->where('email', $email)
            ->whereNotNull('organization_id')
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_MEMBER])
            ->first();

        if (! $user) {
            return $this->neutralResponse();
        }

        $latestChallenge = LoginChallenge::query()
            ->where('email', $email)
            ->latest('created_at')
            ->first();

        if ($latestChallenge && $latestChallenge->created_at->diffInSeconds(now()) < (int) config('otp.resend_cooldown_seconds')) {
            return [
                'challenge_id' => $latestChallenge->public_id,
                'expires_in' => max(0, now()->diffInSeconds($latestChallenge->expires_at)),
                'retry_after' => max(1, (int) config('otp.resend_cooldown_seconds') - $latestChallenge->created_at->diffInSeconds(now())),
            ];
        }

        $displayCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes((int) config('otp.expires_minutes'));
        $challenge = DB::transaction(function () use ($email, $user, $displayCode, $expiresAt, $ipAddress, $userAgent) {
            LoginChallenge::query()
                ->where('email', $email)
                ->whereNull('consumed_at')
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            return LoginChallenge::query()->create([
                'public_id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'email' => $email,
                'code_hash' => Hash::make($displayCode),
                'max_attempts' => (int) config('otp.max_attempts'),
                'expires_at' => $expiresAt,
                'requested_ip' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        });

        try {
            Mail::to($user->email)->send(new LoginOtpMail($user, self::formatCode($displayCode), $expiresAt));
            $challenge->forceFill(['delivery_status' => 'sent'])->save();
        } catch (Throwable $exception) {
            $challenge->forceFill([
                'delivery_status' => 'failed',
                'delivery_error' => str($exception->getMessage())->limit(1000),
                'invalidated_at' => now(),
            ])->save();

            report($exception);
            throw $exception;
        }

        if (config('otp.log_codes') && app()->environment('local')) {
            Log::channel('stderr')->info('Passwordless OTP issued', [
                'challenge_id' => $challenge->public_id,
                'email' => $user->email,
                'code' => self::formatCode($displayCode),
                'expires_at' => $expiresAt->toIso8601String(),
            ]);
        }

        return [
            'challenge_id' => $challenge->public_id,
            'expires_in' => max(0, now()->diffInSeconds($expiresAt)),
            'retry_after' => (int) config('otp.resend_cooldown_seconds'),
        ];
    }

    public function verifyCode(string $challengeId, string $email, string $code): User
    {
        $email = self::normalizeEmail($email);
        $normalizedCode = self::normalizeCode($code);

        $result = DB::transaction(function () use ($challengeId, $email, $normalizedCode) {
            $challenge = LoginChallenge::query()
                ->where('public_id', $challengeId)
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $challenge) {
                throw new LoginChallengeException('invalid', 'That sign-in code is not valid.');
            }

            if ($challenge->consumed_at || $challenge->invalidated_at) {
                throw new LoginChallengeException('used', 'That sign-in code is no longer available.', 410);
            }

            if ($challenge->locked_at || $challenge->attempts >= $challenge->max_attempts) {
                throw new LoginChallengeException('locked', 'Too many attempts. Request a new sign-in code.', 429);
            }

            if ($challenge->isExpired()) {
                throw new LoginChallengeException('expired', 'That sign-in code has expired. Request a new one.', 410);
            }

            if ($normalizedCode === null || ! Hash::check($normalizedCode, $challenge->code_hash)) {
                $this->registerFailedAttempt($challenge);
                $message = $challenge->locked_at
                    ? 'Too many attempts. Request a new sign-in code.'
                    : 'That sign-in code is not valid.';
                $status = $challenge->locked_at ? 429 : 422;
                $reason = $challenge->locked_at ? 'locked' : 'invalid';

                return new LoginChallengeException($reason, $message, $status);
            }

            $challenge->forceFill(['consumed_at' => now()])->save();
            $user = $challenge->user;

            if (! $user || ! $user->organization_id) {
                throw new LoginChallengeException('invalid', 'That sign-in code is not valid.');
            }

            return $user;
        });

        if ($result instanceof LoginChallengeException) {
            throw $result;
        }

        return $result;
    }

    public static function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    public static function formatCode(string $code): string
    {
        return substr($code, 0, 3).'-'.substr($code, 3, 3);
    }

    private static function normalizeCode(string $code): ?string
    {
        $code = trim($code);

        if (! preg_match('/^[0-9\s-]{6,7}$/', $code)) {
            return null;
        }

        $normalized = str_replace([' ', '-'], '', $code);

        return preg_match('/^\d{6}$/', $normalized) ? $normalized : null;
    }

    private function registerFailedAttempt(LoginChallenge $challenge): void
    {
        $attempts = $challenge->attempts + 1;
        $challenge->forceFill([
            'attempts' => $attempts,
            'last_attempt_at' => now(),
            'locked_at' => $attempts >= $challenge->max_attempts ? now() : null,
        ])->save();
    }

    private function rateLimitRetryAfter(string $email, ?string $ipAddress): ?int
    {
        foreach ($this->rateLimitKeys($email, $ipAddress) as $key) {
            if (RateLimiter::tooManyAttempts($key, (int) config('otp.max_requests_per_hour'))) {
                return RateLimiter::availableIn($key);
            }
        }

        return null;
    }

    private function hitRateLimits(string $email, ?string $ipAddress): void
    {
        foreach ($this->rateLimitKeys($email, $ipAddress) as $key) {
            RateLimiter::hit($key, 3600);
        }
    }

    private function rateLimitKeys(string $email, ?string $ipAddress): array
    {
        $keys = ['otp:email:'.hash('sha256', $email)];

        if ($ipAddress) {
            $keys[] = 'otp:ip:'.hash('sha256', $ipAddress);
        }

        return $keys;
    }

    private function neutralResponse(int $retryAfter = 0): array
    {
        return [
            'challenge_id' => (string) Str::uuid(),
            'expires_in' => (int) config('otp.expires_minutes') * 60,
            'retry_after' => $retryAfter,
        ];
    }
}
