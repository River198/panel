<?php

namespace river\Providers;

use river\Models\User;
use river\Models\Server;
use river\Models\Subuser;
use river\Models\EggVariable;
use river\Observers\UserObserver;
use river\Observers\ServerObserver;
use river\Observers\SubuserObserver;
use river\Observers\EggVariableObserver;
use river\Listeners\Auth\AuthenticationListener;
use river\Events\Server\Installed as ServerInstalledEvent;
use river\Notifications\ServerInstalled as ServerInstalledNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ServerInstalledEvent::class => [ServerInstalledNotification::class],
    ];

    protected $subscribe = [
        AuthenticationListener::class,
    ];

    /**
     * Boots the service provider and registers model event listeners.
     */
    public function boot()
    {
        parent::boot();

        User::observe(UserObserver::class);
        Server::observe(ServerObserver::class);
        Subuser::observe(SubuserObserver::class);
        EggVariable::observe(EggVariableObserver::class);
    }

    public function shouldDiscoverEvents()
    {
        return true;
    }
}
