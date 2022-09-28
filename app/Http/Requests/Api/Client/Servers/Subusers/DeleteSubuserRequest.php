<?php

namespace river\Http\Requests\Api\Client\Servers\Subusers;

use river\Models\Permission;

class DeleteSubuserRequest extends SubuserRequest
{
    /**
     * @return string
     */
    public function permission()
    {
        return Permission::ACTION_USER_DELETE;
    }
}
