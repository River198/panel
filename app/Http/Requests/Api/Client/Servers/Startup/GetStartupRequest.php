<?php

namespace river\Http\Requests\Api\Client\Servers\Startup;

use river\Models\Permission;
use river\Http\Requests\Api\Client\ClientApiRequest;

class GetStartupRequest extends ClientApiRequest
{
    /**
     * @return string
     */
    public function permission()
    {
        return Permission::ACTION_STARTUP_READ;
    }
}
