<?php

namespace river\Http\Requests\Api\Application\Nests\Eggs;

use river\Services\Acl\Api\AdminAcl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class GetEggsRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_EGGS;

    /**
     * @var int
     */
    protected $permission = AdminAcl::READ;
}
