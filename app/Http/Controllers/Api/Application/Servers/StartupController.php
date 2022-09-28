<?php

namespace river\Http\Controllers\Api\Application\Servers;

use river\Models\User;
use river\Models\Server;
use river\Services\Servers\StartupModificationService;
use river\Transformers\Api\Application\ServerTransformer;
use river\Http\Controllers\Api\Application\ApplicationApiController;
use river\Http\Requests\Api\Application\Servers\UpdateServerStartupRequest;

class StartupController extends ApplicationApiController
{
    /**
     * @var \river\Services\Servers\StartupModificationService
     */
    private $modificationService;

    /**
     * StartupController constructor.
     */
    public function __construct(StartupModificationService $modificationService)
    {
        parent::__construct();

        $this->modificationService = $modificationService;
    }

    /**
     * Update the startup and environment settings for a specific server.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function index(UpdateServerStartupRequest $request, Server $server): array
    {
        $server = $this->modificationService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($server, $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
