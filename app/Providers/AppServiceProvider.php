<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        RateLimiter::for('sendSms', function (Request $request) {
            return Limit::perMinute(env('SMS_LIMIT_PER_MINUTE', 5));
        });
    }
}
