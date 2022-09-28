<?php

namespace river\Http\Requests\Api\Client\Servers\Files;

use river\Models\Permission;
use river\Contracts\Http\ClientPermissionsRequest;
use river\Http\Requests\Api\Client\ClientApiRequest;

class CopyFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'location' => 'required|string',
        ];
    }
}
