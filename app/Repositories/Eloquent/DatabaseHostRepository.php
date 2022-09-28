<?php

namespace river\Repositories\Eloquent;

use Illuminate\Support\Collection;
use river\Models\DatabaseHost;
use river\Contracts\Repository\DatabaseHostRepositoryInterface;

class DatabaseHostRepository extends EloquentRepository implements DatabaseHostRepositoryInterface
{
    /**
     * Return the model backing this repository.
     *
     * @return string
     */
    public function model()
    {
        return DatabaseHost::class;
    }

    /**
     * Return database hosts with a count of databases and the node
     * information for which it is attached.
     */
    public function getWithViewDetails(): Collection
    {
        return $this->getBuilder()->withCount('databases')->with('node')->get();
    }
}
