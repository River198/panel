<?php

namespace river\Http\Requests\Api\Client\Servers\Settings;

use river\Models\Permission;
use river\Http\Requests\Api\Client\ClientApiRequest;

class ReinstallServerRequest extends ClientApiRequest
{
    /**
     * @return string
     */
    public function permission()
    {
        return Permission::ACTION_SETTINGS_REINSTALL;
    }
}
