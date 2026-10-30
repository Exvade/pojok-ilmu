<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $identity = (string) $request->input('user_id', $request->input('token', ''));

            return Limit::perMinute(5)->by($request->ip() . '|' . hash('sha256', mb_strtolower(trim($identity))));
        });

        Blueprint::macro('mwtTimestamps', function () {
            /** @var Blueprint $this */
            $this->timestamps();
            $this->uuid('created_by')->nullable();
            $this->uuid('updated_by')->nullable();
        });
    }
}
