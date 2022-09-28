<?php

namespace river\Providers;

use Laravel\Sanctum\Sanctum;
use river\Models\ApiKey;
use river\Models\Server;
use river\Policies\ServerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Server::class => ServerPolicy::class,
    ];

    public function boot()
    {
        Sanctum::usePersonalAccessTokenModel(ApiKey::class);

        $this->registerPolicies();
    }

    public function register()
    {
        Sanctum::ignoreMigrations();
    }
}
