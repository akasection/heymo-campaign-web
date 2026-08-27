<?php

namespace App\Services;

use App\Models\AuthSession;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use RuntimeException;
use UnexpectedValueException;

class JwtService
{
    public function issue(User $user, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $now = now();
        $expiresAt = $now->copy()->addMinutes((int) config('jwt.ttl_minutes'));
        $sessionId = (string) Str::uuid();

        $authSession = $user->authSessions()->create([
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        $claims = [
            'iss' => $this->issuer(),
            'aud' => $this->audience(),
            'sub' => (string) $user->getAuthIdentifier(),
            'sid' => $sessionId,
            'jti' => $sessionId,
            'role' => $user->role,
            'org_id' => $user->organization_id,
            'iat' => $now->timestamp,
            'nbf' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
        ];

        return [
            'token' => JWT::encode($claims, $this->secret(), $this->algorithm()),
            'session' => $authSession,
            'expires_at' => $expiresAt,
        ];
    }

    public function decode(string $token): object
    {
        $claims = JWT::decode($token, new Key($this->secret(), $this->algorithm()));

        $this->validateClaims($claims);

        return $claims;
    }

    public function activeSession(object $claims): ?AuthSession
    {
        $authSession = AuthSession::query()
            ->where('session_id', (string) $claims->sid)
            ->where('user_id', (int) $claims->sub)
            ->first();

        if (! $authSession || ! $authSession->isActive()) {
            return null;
        }

        $authSession->forceFill(['last_used_at' => now()])->saveQuietly();

        return $authSession;
    }

    private function validateClaims(object $claims): void
    {
        foreach (['iss', 'aud', 'sub', 'sid', 'jti', 'iat', 'exp'] as $claim) {
            if (! property_exists($claims, $claim)) {
                throw new UnexpectedValueException('Required JWT claim is missing.');
            }
        }

        if ($claims->iss !== $this->issuer()) {
            throw new UnexpectedValueException('JWT issuer is invalid.');
        }

        $audiences = is_array($claims->aud) ? $claims->aud : [$claims->aud];
        if (! in_array($this->audience(), $audiences, true)) {
            throw new UnexpectedValueException('JWT audience is invalid.');
        }

        if (! is_numeric($claims->iat) || ! is_numeric($claims->exp)) {
            throw new UnexpectedValueException('JWT time claims are invalid.');
        }

        if ((int) $claims->exp <= (int) $claims->iat) {
            throw new UnexpectedValueException('JWT expiry is invalid.');
        }

        if (! is_numeric($claims->sub) || ! is_string($claims->sid) || $claims->sid !== $claims->jti) {
            throw new UnexpectedValueException('JWT identity claims are invalid.');
        }
    }

    private function secret(): string
    {
        $secret = config('jwt.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new RuntimeException('JWT_SECRET or APP_KEY must be configured.');
        }

        return $secret;
    }

    private function algorithm(): string
    {
        $algorithm = (string) config('jwt.algorithm');

        if ($algorithm !== 'HS256') {
            throw new RuntimeException('Only HS256 JWT signing is supported.');
        }

        return $algorithm;
    }

    private function issuer(): string
    {
        return (string) config('jwt.issuer');
    }

    private function audience(): string
    {
        return (string) config('jwt.audience');
    }
}
