<?php

namespace river\Facades;

use Illuminate\Support\Facades\Facade;
use river\Services\Activity\ActivityLogTargetableService;

class LogTarget extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActivityLogTargetableService::class;
    }
}
