<?php

namespace river\Facades;

use Illuminate\Support\Facades\Facade;
use river\Services\Activity\AcitvityLogBatchService;

class LogBatch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AcitvityLogBatchService::class;
    }
}
