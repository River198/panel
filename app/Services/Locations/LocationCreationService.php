<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Locations;

use river\Contracts\Repository\LocationRepositoryInterface;

class LocationCreationService
{
    /**
     * @var \river\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * LocationCreationService constructor.
     */
    public function __construct(LocationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new location.
     *
     * @return \river\Models\Location
     *
     * @throws \river\Exceptions\Model\DataValidationException
     */
    public function handle(array $data)
    {
        return $this->repository->create($data);
    }
}
