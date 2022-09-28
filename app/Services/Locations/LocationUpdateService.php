<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Locations;

use river\Models\Location;
use river\Contracts\Repository\LocationRepositoryInterface;

class LocationUpdateService
{
    /**
     * @var \river\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationUpdateService constructor.
     */
    public function __construct(LocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Update an existing location.
     *
     * @param int|\river\Models\Location $location
     *
     * @return \river\Models\Location
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function handle($location, array $data)
    {
        $location = ($location instanceof Location) ? $location->id : $location;

        return $this->repository->update($location, $data);
    }
}
