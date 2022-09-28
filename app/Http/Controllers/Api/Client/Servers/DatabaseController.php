<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use river\Models\Database;
use river\Facades\Activity;
use river\Repositories\Eloquent\DatabaseRepository;
use river\Services\Databases\DatabasePasswordService;
use river\Transformers\Api\Client\DatabaseTransformer;
use river\Services\Databases\DatabaseManagementService;
use river\Services\Databases\DeployServerDatabaseService;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Http\Requests\Api\Client\Servers\Databases\GetDatabasesRequest;
use river\Http\Requests\Api\Client\Servers\Databases\StoreDatabaseRequest;
use river\Http\Requests\Api\Client\Servers\Databases\DeleteDatabaseRequest;
use river\Http\Requests\Api\Client\Servers\Databases\RotatePasswordRequest;

class DatabaseController extends ClientApiController
{
    /**
     * @var \river\Services\Databases\DeployServerDatabaseService
     */
    private $deployDatabaseService;

    /**
     * @var \river\Repositories\Eloquent\DatabaseRepository
     */
    private $repository;

    /**
     * @var \river\Services\Databases\DatabaseManagementService
     */
    private $managementService;

    /**
     * @var \river\Services\Databases\DatabasePasswordService
     */
    private $passwordService;

    /**
     * DatabaseController constructor.
     */
    public function __construct(
        DatabaseManagementService $managementService,
        DatabasePasswordService $passwordService,
        DatabaseRepository $repository,
        DeployServerDatabaseService $deployDatabaseService
    ) {
        parent::__construct();

        $this->deployDatabaseService = $deployDatabaseService;
        $this->repository = $repository;
        $this->managementService = $managementService;
        $this->passwordService = $passwordService;
    }

    /**
     * Return all of the databases that belong to the given server.
     */
    public function index(GetDatabasesRequest $request, Server $server): array
    {
        return $this->fractal->collection($server->databases)
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * Create a new database for the given server and return it.
     *
     * @throws \Throwable
     * @throws \river\Exceptions\Service\Database\TooManyDatabasesException
     * @throws \river\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException
     */
    public function store(StoreDatabaseRequest $request, Server $server): array
    {
        $database = $this->deployDatabaseService->handle($server, $request->validated());

        Activity::event('server:database.create')
            ->subject($database)
            ->property('name', $database->database)
            ->log();

        return $this->fractal->item($database)
            ->parseIncludes(['password'])
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * Rotates the password for the given server model and returns a fresh instance to
     * the caller.
     *
     * @return array
     *
     * @throws \Throwable
     */
    public function rotatePassword(RotatePasswordRequest $request, Server $server, Database $database)
    {
        $this->passwordService->handle($database);
        $database->refresh();

        Activity::event('server:database.rotate-password')
            ->subject($database)
            ->property('name', $database->database)
            ->log();

        return $this->fractal->item($database)
            ->parseIncludes(['password'])
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * Removes a database from the server.
     *
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function delete(DeleteDatabaseRequest $request, Server $server, Database $database): Response
    {
        $this->managementService->delete($database);

        Activity::event('server:database.delete')
            ->subject($database)
            ->property('name', $database->database)
            ->log();

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
