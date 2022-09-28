<?php

namespace river\Http\Requests\Api\Client\Servers\Schedules;

use river\Models\Permission;

class UpdateScheduleRequest extends StoreScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_UPDATE;
    }
}
