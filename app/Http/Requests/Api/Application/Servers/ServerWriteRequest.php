<?php

namespace river\Http\Requests\Api\Application\Servers;

use river\Services\Acl\Api\AdminAcl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class ServerWriteRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_SERVERS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;
}
