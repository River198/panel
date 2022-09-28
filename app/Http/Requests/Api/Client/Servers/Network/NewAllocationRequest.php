<?php

namespace river\Http\Requests\Api\Client\Servers\Network;

use river\Models\Permission;
use river\Http\Requests\Api\Client\ClientApiRequest;

class NewAllocationRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_ALLOCATION_CREATE;
    }
}
