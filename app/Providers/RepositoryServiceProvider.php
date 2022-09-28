<?php

namespace river\Providers;

use Illuminate\Support\ServiceProvider;
use river\Repositories\Eloquent\EggRepository;
use river\Repositories\Eloquent\NestRepository;
use river\Repositories\Eloquent\NodeRepository;
use river\Repositories\Eloquent\TaskRepository;
use river\Repositories\Eloquent\UserRepository;
use river\Repositories\Eloquent\ApiKeyRepository;
use river\Repositories\Eloquent\ServerRepository;
use river\Repositories\Eloquent\SessionRepository;
use river\Repositories\Eloquent\SubuserRepository;
use river\Repositories\Eloquent\DatabaseRepository;
use river\Repositories\Eloquent\LocationRepository;
use river\Repositories\Eloquent\ScheduleRepository;
use river\Repositories\Eloquent\SettingsRepository;
use river\Repositories\Eloquent\AllocationRepository;
use river\Contracts\Repository\EggRepositoryInterface;
use river\Repositories\Eloquent\EggVariableRepository;
use river\Contracts\Repository\NestRepositoryInterface;
use river\Contracts\Repository\NodeRepositoryInterface;
use river\Contracts\Repository\TaskRepositoryInterface;
use river\Contracts\Repository\UserRepositoryInterface;
use river\Repositories\Eloquent\DatabaseHostRepository;
use river\Contracts\Repository\ApiKeyRepositoryInterface;
use river\Contracts\Repository\ServerRepositoryInterface;
use river\Repositories\Eloquent\ServerVariableRepository;
use river\Contracts\Repository\SessionRepositoryInterface;
use river\Contracts\Repository\SubuserRepositoryInterface;
use river\Contracts\Repository\DatabaseRepositoryInterface;
use river\Contracts\Repository\LocationRepositoryInterface;
use river\Contracts\Repository\ScheduleRepositoryInterface;
use river\Contracts\Repository\SettingsRepositoryInterface;
use river\Contracts\Repository\AllocationRepositoryInterface;
use river\Contracts\Repository\EggVariableRepositoryInterface;
use river\Contracts\Repository\DatabaseHostRepositoryInterface;
use river\Contracts\Repository\ServerVariableRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all of the repository bindings.
     */
    public function register()
    {
        // Eloquent Repositories
        $this->app->bind(AllocationRepositoryInterface::class, AllocationRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
        $this->app->bind(DatabaseRepositoryInterface::class, DatabaseRepository::class);
        $this->app->bind(DatabaseHostRepositoryInterface::class, DatabaseHostRepository::class);
        $this->app->bind(EggRepositoryInterface::class, EggRepository::class);
        $this->app->bind(EggVariableRepositoryInterface::class, EggVariableRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(NestRepositoryInterface::class, NestRepository::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ServerVariableRepositoryInterface::class, ServerVariableRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(SubuserRepositoryInterface::class, SubuserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
