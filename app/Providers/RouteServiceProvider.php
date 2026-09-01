<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('capture', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email')));
            $emailKey = $email === '' ? 'anonymous:'.$request->ip() : hash('sha256', $email);

            return [
                Limit::perMinute((int) config('capture.limits.per_ip_per_minute', 10))->by('capture-ip:'.$request->ip()),
                Limit::perMinute((int) config('capture.limits.per_email_per_minute', 3))->by('capture-email:'.$emailKey),
            ];
        });

        RateLimiter::for('landing', function (Request $request) {
            return Limit::perMinute(60)->by('landing-ip:'.$request->ip());
        });

        RateLimiter::for('open', function (Request $request) {
            return Limit::perMinute(120)->by('open-ip:'.$request->ip());
        });
    }
}
