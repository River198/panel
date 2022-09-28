<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Extensions;

use river\Models\DatabaseHost;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Config\Repository as ConfigRepository;
use river\Contracts\Repository\DatabaseHostRepositoryInterface;

class DynamicDatabaseConnection
{
    public const DB_CHARSET = 'utf8';
    public const DB_COLLATION = 'utf8_unicode_ci';
    public const DB_DRIVER = 'mysql';

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * @var \river\Contracts\Repository\DatabaseHostRepositoryInterface
     */
    protected $repository;

    /**
     * DynamicDatabaseConnection constructor.
     */
    public function __construct(
        ConfigRepository $config,
        DatabaseHostRepositoryInterface $repository,
        Encrypter $encrypter
    ) {
        $this->config = $config;
        $this->encrypter = $encrypter;
        $this->repository = $repository;
    }

    /**
     * Adds a dynamic database connection entry to the runtime config.
     *
     * @param string $connection
     * @param \river\Models\DatabaseHost|int $host
     * @param string $database
     *
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function set($connection, $host, $database = 'mysql')
    {
        if (!$host instanceof DatabaseHost) {
            $host = $this->repository->find($host);
        }

        $this->config->set('database.connections.' . $connection, [
            'driver' => self::DB_DRIVER,
            'host' => $host->host,
            'port' => $host->port,
            'database' => $database,
            'username' => $host->username,
            'password' => $this->encrypter->decrypt($host->password),
            'charset' => self::DB_CHARSET,
            'collation' => self::DB_COLLATION,
        ]);
    }
}
