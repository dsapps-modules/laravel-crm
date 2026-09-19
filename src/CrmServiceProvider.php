<?php

namespace DsApps\LaravelCrm;

use DsApps\LaravelCrm\Contracts\AuthorizationResolver;
use DsApps\LaravelCrm\Http\Middleware\AuthorizeCrm;
use DsApps\LaravelCrm\Support\DefaultAuthorizationResolver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/crm.php', 'crm');
        $this->app->bind(AuthorizationResolver::class, fn ($app) => $app->make(config('crm.authorization_resolver', DefaultAuthorizationResolver::class)));
    }

    public function boot(): void
    {
        RateLimiter::for('whatsapp-webhooks', fn () => Limit::perMinute(120));
        RateLimiter::for('brevo-webhooks', fn () => Limit::perMinute(120));
        $this->publishes([__DIR__.'/../config/crm.php' => config_path('crm.php')], 'crm-config');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $router = $this->app['router'];
        $router->aliasMiddleware('crm.authorize', AuthorizeCrm::class);
    }
}
