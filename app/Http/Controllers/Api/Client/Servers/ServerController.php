<?php

namespace river\Http\Controllers\Api\Client\Servers;

use river\Models\Server;
use river\Repositories\Eloquent\SubuserRepository;
use river\Transformers\Api\Client\ServerTransformer;
use river\Services\Servers\GetUserPermissionsService;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Http\Requests\Api\Client\Servers\GetServerRequest;

class ServerController extends ClientApiController
{
    /**
     * @var \river\Repositories\Eloquent\SubuserRepository
     */
    private $repository;

    /**
     * @var \river\Services\Servers\GetUserPermissionsService
     */
    private $permissionsService;

    /**
     * ServerController constructor.
     */
    public function __construct(GetUserPermissionsService $permissionsService, SubuserRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Transform an individual server into a response that can be consumed by a
     * client using the API.
     */
    public function index(GetServerRequest $request, Server $server): array
    {
        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->addMeta([
                'is_server_owner' => $request->user()->id === $server->owner_id,
                'user_permissions' => $this->permissionsService->handle($server, $request->user()),
            ])
            ->toArray();
    }
}
