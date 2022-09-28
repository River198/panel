<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Eggs;

use Ramsey\Uuid\Uuid;
use river\Models\Egg;
use river\Contracts\Repository\EggRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use river\Exceptions\Service\Egg\NoParentConfigurationFoundException;

// When a mommy and a daddy river really like each other...
class EggCreationService
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \river\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * EggCreationService constructor.
     */
    public function __construct(ConfigRepository $config, EggRepositoryInterface $repository)
    {
        $this->config = $config;
        $this->repository = $repository;
    }

    /**
     * Create a new service option and assign it to the given service.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Service\Egg\NoParentConfigurationFoundException
     */
    public function handle(array $data): Egg
    {
        $data['config_from'] = array_get($data, 'config_from');
        if (!is_null($data['config_from'])) {
            $results = $this->repository->findCountWhere([
                ['nest_id', '=', array_get($data, 'nest_id')],
                ['id', '=', array_get($data, 'config_from')],
            ]);

            if ($results !== 1) {
                throw new NoParentConfigurationFoundException(trans('exceptions.nest.egg.must_be_child'));
            }
        }

        return $this->repository->create(array_merge($data, [
            'uuid' => Uuid::uuid4()->toString(),
            'author' => $this->config->get('river.service.author'),
        ]), true, true);
    }
}
