<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Carbon\Carbon;
use river\Models\Server;
use Illuminate\Cache\Repository;
use river\Transformers\Api\Client\StatsTransformer;
use river\Repositories\Wings\DaemonServerRepository;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Http\Requests\Api\Client\Servers\GetServerRequest;

class ResourceUtilizationController extends ClientApiController
{
    private DaemonServerRepository $repository;

    private Repository $cache;

    /**
     * ResourceUtilizationController constructor.
     */
    public function __construct(Repository $cache, DaemonServerRepository $repository)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * Return the current resource utilization for a server. This value is cached for up to
     * 20 seconds at a time to ensure that repeated requests to this endpoint do not cause
     * a flood of unnecessary API calls.
     *
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function __invoke(GetServerRequest $request, Server $server): array
    {
        $key = "resources:{$server->uuid}";
        $stats = $this->cache->remember($key, Carbon::now()->addSeconds(20), function () use ($server) {
            return $this->repository->setServer($server)->getDetails();
        });

        return $this->fractal->item($stats)
            ->transformWith($this->getTransformer(StatsTransformer::class))
            ->toArray();
    }
}
