<?php

namespace river\Http\Requests\Api\Client\Servers\Files;

use river\Models\Permission;
use river\Contracts\Http\ClientPermissionsRequest;
use river\Http\Requests\Api\Client\ClientApiRequest;

class RenameFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    /**
     * The permission the user is required to have in order to perform this
     * request action.
     */
    public function permission(): string
    {
        return Permission::ACTION_FILE_UPDATE;
    }

    public function rules(): array
    {
        return [
            'root' => 'required|nullable|string',
            'files' => 'required|array',
            'files.*' => 'array',
            'files.*.to' => 'required|string',
            'files.*.from' => 'required|string',
        ];
    }
}
