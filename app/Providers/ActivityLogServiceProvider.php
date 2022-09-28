<?php

namespace river\Providers;

use Illuminate\Support\ServiceProvider;
use river\Services\Activity\AcitvityLogBatchService;
use river\Services\Activity\ActivityLogTargetableService;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Registers the necessary activity logger singletons scoped to the individual
     * request instances.
     */
    public function register()
    {
        $this->app->scoped(AcitvityLogBatchService::class);
        $this->app->scoped(ActivityLogTargetableService::class);
    }
}
