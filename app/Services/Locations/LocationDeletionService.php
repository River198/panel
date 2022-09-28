<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Locations;

use Webmozart\Assert\Assert;
use river\Models\Location;
use river\Contracts\Repository\NodeRepositoryInterface;
use river\Contracts\Repository\LocationRepositoryInterface;
use river\Exceptions\Service\Location\HasActiveNodesException;

class LocationDeletionService
{
    /**
     * @var \river\Contracts\Repository\NodeRepositoryInterface
     */
    protected $nodeRepository;

    /**
     * @var \river\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationDeletionService constructor.
     */
    public function __construct(
        LocationRepositoryInterface $repository,
        NodeRepositoryInterface $nodeRepository
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->repository = $repository;
    }

    /**
     * Delete an existing location.
     *
     * @param int|\river\Models\Location $location
     *
     * @return int|null
     *
     * @throws \river\Exceptions\Service\Location\HasActiveNodesException
     */
    public function handle($location)
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        Assert::integerish($location, 'First argument passed to handle must be numeric or an instance of ' . Location::class . ', received %s.');

        $count = $this->nodeRepository->findCountWhere([['location_id', '=', $location]]);
        if ($count > 0) {
            throw new HasActiveNodesException(trans('exceptions.locations.has_nodes'));
        }

        return $this->repository->delete($location);
    }
}
