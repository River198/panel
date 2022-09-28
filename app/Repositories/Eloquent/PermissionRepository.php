<?php

namespace river\Repositories\Eloquent;

use Exception;
use river\Contracts\Repository\PermissionRepositoryInterface;

class PermissionRepository extends EloquentRepository implements PermissionRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function model()
    {
        throw new Exception('This functionality is not implemented.');
    }
}
