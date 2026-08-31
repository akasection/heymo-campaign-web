<?php

namespace App\Http\Middleware;

use App\Models\AuthSession;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthenticateJwt
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthenticated();
        }

        try {
            $claims = $this->jwt->decode($token);
            $authSession = $this->jwt->activeSession($claims);
            $user = $authSession?->user;

            if (! $authSession instanceof AuthSession || ! $user) {
                return $this->unauthenticated();
            }

            if ((string) $user->organization_id !== (string) ($claims->org_id ?? '') || $user->role !== ($claims->role ?? null)) {
                return $this->unauthenticated();
            }

            $request->setUserResolver(fn () => $user);
            Auth::setUser($user);
            $request->attributes->set('auth_session', $authSession);
            $request->attributes->set('jwt_claims', $claims);

            return $next($request);
        } catch (Throwable) {
            return $this->unauthenticated();
        }
    }

    private function unauthenticated()
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
