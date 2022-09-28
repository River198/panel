<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use river\Facades\Activity;
use river\Repositories\Wings\DaemonPowerRepository;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Http\Requests\Api\Client\Servers\SendPowerRequest;

class PowerController extends ClientApiController
{
    /**
     * @var \river\Repositories\Wings\DaemonPowerRepository
     */
    private $repository;

    /**
     * PowerController constructor.
     */
    public function __construct(DaemonPowerRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Send a power action to a server.
     */
    public function index(SendPowerRequest $request, Server $server): Response
    {
        $this->repository->setServer($server)->send(
            $request->input('signal')
        );

        Activity::event(strtolower("server:power.{$request->input('signal')}"))->log();

        return $this->returnNoContent();
    }
}
