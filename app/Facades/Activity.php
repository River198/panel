<?php

namespace river\Facades;

use Illuminate\Support\Facades\Facade;
use river\Services\Activity\ActivityLogService;

class Activity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActivityLogService::class;
    }
}
