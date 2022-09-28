<?php

namespace river\Http\Requests\Api\Client\Servers\Subusers;

use river\Models\Permission;

class UpdateSubuserRequest extends SubuserRequest
{
    /**
     * @return string
     */
    public function permission()
    {
        return Permission::ACTION_USER_UPDATE;
    }

    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ];
    }
}
