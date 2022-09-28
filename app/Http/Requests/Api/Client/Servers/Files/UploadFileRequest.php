<?php

namespace river\Http\Requests\Api\Client\Servers\Files;

use river\Models\Permission;
use river\Http\Requests\Api\Client\ClientApiRequest;

class UploadFileRequest extends ClientApiRequest
{
    /**
     * @return string
     */
    public function permission()
    {
        return Permission::ACTION_FILE_CREATE;
    }
}
