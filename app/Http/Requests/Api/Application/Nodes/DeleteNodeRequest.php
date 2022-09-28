<?php

namespace river\Http\Requests\Api\Application\Nodes;

use river\Services\Acl\Api\AdminAcl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteNodeRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = AdminAcl::RESOURCE_NODES;

    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;
}
