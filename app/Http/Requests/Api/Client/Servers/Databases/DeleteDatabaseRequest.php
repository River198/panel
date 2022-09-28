<?php

namespace river\Http\Requests\Api\Client\Servers\Databases;

use river\Models\Permission;
use river\Contracts\Http\ClientPermissionsRequest;
use river\Http\Requests\Api\Client\ClientApiRequest;

class DeleteDatabaseRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_DELETE;
    }
}
