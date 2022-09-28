<?php

namespace river\Contracts\Repository;

interface AllocationRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all of the allocations that exist for a node that are not currently
     * allocated.
     */
    public function getUnassignedAllocationIds(int $node): array;

    /**
     * Return a single allocation from those meeting the requirements.
     *
     * @return \river\Models\Allocation|null
     */
    public function getRandomAllocation(array $nodes, array $ports, bool $dedicated = false);
}
