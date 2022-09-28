<?php

namespace river\Contracts\Repository;

interface SettingsRepositoryInterface extends RepositoryInterface
{
    /**
     * Store a new persistent setting in the database.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function set(string $key, string $value = null);

    /**
     * Retrieve a persistent setting from the database.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default);

    /**
     * Remove a key from the database cache.
     */
    public function forget(string $key);
}
