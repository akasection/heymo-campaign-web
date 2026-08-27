<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\LoginChallengeException;
use App\Http\Controllers\Controller;
use App\Models\AuthSession;
use App\Services\JwtService;
use App\Services\PasswordlessLoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PasswordlessLoginController extends Controller
{
    public function __construct(
        private PasswordlessLoginService $passwordlessLogin,
        private JwtService $jwt,
    ) {}

    public function requestCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $challenge = $this->passwordlessLogin->requestCode(
                $validated['email'],
                $request->ip(),
                $request->userAgent(),
            );

            return response()->json([
                'message' => 'If that address is provisioned, a sign-in code is on its way.',
                'challenge_id' => $challenge['challenge_id'],
                'expires_in' => $challenge['expires_in'],
                'retry_after' => $challenge['retry_after'],
            ], 202);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'We could not send a sign-in code right now. Please try again shortly.',
            ], 503);
        }
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
        ]);

        try {
            $user = $this->passwordlessLogin->verifyCode(
                $validated['challenge_id'],
                $validated['email'],
                $validated['code'],
            );

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            try {
                $issued = $this->jwt->issue($user, $request->ip(), $request->userAgent());
            } catch (Throwable $exception) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw $exception;
            }

            return $this->authenticationResponse($user, $issued);
        } catch (LoginChallengeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'reason' => $exception->reason,
            ], $exception->status);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'We could not complete sign-in right now. Please try again shortly.',
            ], 503);
        }
    }

    public function issueForSession(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $issued = $this->jwt->issue($user, $request->ip(), $request->userAgent());

            return $this->authenticationResponse($user, $issued);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'We could not refresh access right now. Please try again shortly.',
            ], 503);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $authSession = $request->attributes->get('auth_session');

        if ($authSession instanceof AuthSession && $authSession->revoked_at === null) {
            $authSession->forceFill(['revoked_at' => now()])->save();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Signed out.']);
    }

    private function authenticationResponse($user, array $issued): JsonResponse
    {
        return response()->json([
            'message' => 'Signed in successfully.',
            'access_token' => $issued['token'],
            'token_type' => 'Bearer',
            'expires_in' => max(0, now()->diffInSeconds($issued['expires_at'])),
            'user' => $user->toAuthPayload(),
        ]);
    }
}
