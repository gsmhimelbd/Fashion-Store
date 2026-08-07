<?php

namespace App\Providers;

use App\Repositories\VCardRepository;
use App\Repositories\VCardRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VCardRepositoryInterface::class, VCardRepository::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        RateLimiter::for('appointments', fn (Request $request) => Limit::perHour(10)->by($request->ip()));
        RateLimiter::for('leads', fn (Request $request) => Limit::perHour(20)->by($request->ip()));
    }
}
