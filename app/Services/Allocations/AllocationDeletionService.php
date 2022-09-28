<?php

namespace river\Services\Allocations;

use river\Models\Allocation;
use river\Contracts\Repository\AllocationRepositoryInterface;
use river\Exceptions\Service\Allocation\ServerUsingAllocationException;

class AllocationDeletionService
{
    /**
     * @var \river\Contracts\Repository\AllocationRepositoryInterface
     */
    private $repository;

    /**
     * AllocationDeletionService constructor.
     */
    public function __construct(AllocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Delete an allocation from the database only if it does not have a server
     * that is actively attached to it.
     *
     * @return int
     *
     * @throws \river\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function handle(Allocation $allocation)
    {
        if (!is_null($allocation->server_id)) {
            throw new ServerUsingAllocationException(trans('exceptions.allocations.server_using'));
        }

        return $this->repository->delete($allocation->id);
    }
}
