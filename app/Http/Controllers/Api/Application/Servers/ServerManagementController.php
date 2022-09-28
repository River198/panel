<?php

namespace river\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use river\Services\Servers\SuspensionService;
use river\Services\Servers\ReinstallServerService;
use river\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use river\Http\Controllers\Api\Application\ApplicationApiController;

class ServerManagementController extends ApplicationApiController
{
    /**
     * @var \river\Services\Servers\ReinstallServerService
     */
    private $reinstallServerService;

    /**
     * @var \river\Services\Servers\SuspensionService
     */
    private $suspensionService;

    /**
     * SuspensionController constructor.
     */
    public function __construct(
        ReinstallServerService $reinstallServerService,
        SuspensionService $suspensionService
    ) {
        parent::__construct();

        $this->reinstallServerService = $reinstallServerService;
        $this->suspensionService = $suspensionService;
    }

    /**
     * Suspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function suspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server, SuspensionService::ACTION_SUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Unsuspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function unsuspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Mark a server as needing to be reinstalled.
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function reinstall(ServerWriteRequest $request, Server $server): Response
    {
        $this->reinstallServerService->handle($server);

        return $this->returnNoContent();
    }
}
