<?php

namespace river\Http\Requests\Api\Application\Servers\Databases;

use river\Services\Acl\Api\AdminAcl;

class ServerDatabaseWriteRequest extends GetServerDatabasesRequest
{
    /**
     * @var int
     */
    protected $permission = AdminAcl::WRITE;
}
