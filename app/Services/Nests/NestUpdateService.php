<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Nests;

use river\Contracts\Repository\NestRepositoryInterface;

class NestUpdateService
{
    /**
     * @var \river\Contracts\Repository\NestRepositoryInterface
     */
    protected $repository;

    /**
     * NestUpdateService constructor.
     */
    public function __construct(NestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Update a nest and prevent changing the author once it is set.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function handle(int $nest, array $data)
    {
        if (!is_null(array_get($data, 'author'))) {
            unset($data['author']);
        }

        $this->repository->withoutFreshModel()->update($nest, $data);
    }
}
