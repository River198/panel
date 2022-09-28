<?php

namespace river\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use river\Services\Servers\ServerCreationService;
use river\Services\Servers\ServerDeletionService;
use river\Contracts\Repository\ServerRepositoryInterface;
use river\Transformers\Api\Application\ServerTransformer;
use river\Http\Requests\Api\Application\Servers\GetServerRequest;
use river\Http\Requests\Api\Application\Servers\GetServersRequest;
use river\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use river\Http\Requests\Api\Application\Servers\StoreServerRequest;
use river\Http\Controllers\Api\Application\ApplicationApiController;

class ServerController extends ApplicationApiController
{
    /**
     * @var \river\Services\Servers\ServerCreationService
     */
    private $creationService;

    /**
     * @var \river\Services\Servers\ServerDeletionService
     */
    private $deletionService;

    /**
     * @var \river\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * ServerController constructor.
     */
    public function __construct(
        ServerCreationService $creationService,
        ServerDeletionService $deletionService,
        ServerRepositoryInterface $repository
    ) {
        parent::__construct();

        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
    }

    /**
     * Return all of the servers that currently exist on the Panel.
     */
    public function index(GetServersRequest $request): array
    {
        $servers = QueryBuilder::for(Server::query())
            ->allowedFilters(['uuid', 'uuidShort', 'name', 'description', 'image', 'external_id'])
            ->allowedSorts(['id', 'uuid'])
            ->paginate($request->query('per_page') ?? 50);

        return $this->fractal->collection($servers)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Create a new server on the system.
     *
     * @throws \Throwable
     * @throws \Illuminate\Validation\ValidationException
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     * @throws \river\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \river\Exceptions\Service\Deployment\NoViableNodeException
     */
    public function store(StoreServerRequest $request): JsonResponse
    {
        $server = $this->creationService->handle($request->validated(), $request->getDeploymentObject());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->respond(201);
    }

    /**
     * Show a single server transformed for the application API.
     */
    public function view(GetServerRequest $request, Server $server): array
    {
        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * @throws \river\Exceptions\DisplayException
     */
    public function delete(ServerWriteRequest $request, Server $server, string $force = ''): Response
    {
        $this->deletionService->withForce($force === 'force')->handle($server);

        return $this->returnNoContent();
    }
}
