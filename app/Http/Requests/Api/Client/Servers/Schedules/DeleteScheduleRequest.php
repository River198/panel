<?php

namespace river\Http\Requests\Api\Client\Servers\Schedules;

use river\Models\Permission;

class DeleteScheduleRequest extends ViewScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_DELETE;
    }
}
