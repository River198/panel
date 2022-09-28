<?php

namespace river\Repositories\Eloquent;

use river\Models\ServerVariable;
use river\Contracts\Repository\ServerVariableRepositoryInterface;

class ServerVariableRepository extends EloquentRepository implements ServerVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return ServerVariable::class;
    }
}
