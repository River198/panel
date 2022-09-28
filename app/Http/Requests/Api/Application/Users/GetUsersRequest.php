<?php

namespace river\Http\Requests\Api\Application\Users;

use river\Services\Acl\Api\AdminAcl as Acl;
use river\Http\Requests\Api\Application\ApplicationApiRequest;

class GetUsersRequest extends ApplicationApiRequest
{
    /**
     * @var string
     */
    protected $resource = Acl::RESOURCE_USERS;

    /**
     * @var int
     */
    protected $permission = Acl::READ;
}
