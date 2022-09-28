<?php

namespace river\Http\Requests\Api\Application\Allocations;

use river\Services\Acl\Api\AdminAcl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class GetAllocationsRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_ALLOCATIONS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;
}
