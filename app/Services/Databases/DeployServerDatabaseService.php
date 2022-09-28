<?php

namespace river\Services\Databases;

use Webmozart\Assert\Assert;
use river\Models\Server;
use river\Models\Database;
use river\Models\DatabaseHost;
use river\Exceptions\Service\Database\NoSuitableDatabaseHostException;

class DeployServerDatabaseService
{
    /**
     * @var \river\Services\Databases\DatabaseManagementService
     */
    private $managementService;

    /**
     * ServerDatabaseCreationService constructor.
     *
     * @param \river\Services\Databases\DatabaseManagementService $managementService
     */
    public function __construct(DatabaseManagementService $managementService)
    {
        $this->managementService = $managementService;
    }

    /**
     * @throws \Throwable
     * @throws \river\Exceptions\Service\Database\TooManyDatabasesException
     * @throws \river\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException
     */
    public function handle(Server $server, array $data): Database
    {
        Assert::notEmpty($data['database'] ?? null);
        Assert::notEmpty($data['remote'] ?? null);

        $hosts = DatabaseHost::query()->get()->toBase();
        if ($hosts->isEmpty()) {
            throw new NoSuitableDatabaseHostException();
        } else {
            $nodeHosts = $hosts->where('node_id', $server->node_id)->toBase();

            if ($nodeHosts->isEmpty() && !config('river.client_features.databases.allow_random')) {
                throw new NoSuitableDatabaseHostException();
            }
        }

        return $this->managementService->create($server, [
            'database_host_id' => $nodeHosts->isEmpty()
                ? $hosts->random()->id
                : $nodeHosts->random()->id,
            'database' => DatabaseManagementService::generateUniqueDatabaseName($data['database'], $server->id),
            'remote' => $data['remote'],
        ]);
    }
}
