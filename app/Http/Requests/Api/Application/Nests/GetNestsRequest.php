<?php

namespace river\Http\Requests\Api\Application\Nests;

use river\Services\Acl\Api\AdminAcl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class GetNestsRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_NESTS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;
}
