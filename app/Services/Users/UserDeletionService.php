<?php

/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Services\Users;

use river\Models\User;
use river\Exceptions\DisplayException;
use Illuminate\Contracts\Translation\Translator;
use river\Contracts\Repository\UserRepositoryInterface;
use river\Contracts\Repository\ServerRepositoryInterface;

class UserDeletionService
{
    /**
     * @var \river\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * @var \river\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * DeletionService constructor.
     */
    public function __construct(
        ServerRepositoryInterface $serverRepository,
        Translator $translator,
        UserRepositoryInterface $repository
    ) {
        $this->repository = $repository;
        $this->translator = $translator;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Delete a user from the panel only if they have no servers attached to their account.
     *
     * @param int|\river\Models\User $user
     *
     * @return bool|null
     *
     * @throws \river\Exceptions\DisplayException
     */
    public function handle($user)
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $servers = $this->serverRepository->setColumns('id')->findCountWhere([['owner_id', '=', $user]]);
        if ($servers > 0) {
            throw new DisplayException($this->translator->get('admin/user.exceptions.user_has_servers'));
        }

        return $this->repository->delete($user);
    }
}
