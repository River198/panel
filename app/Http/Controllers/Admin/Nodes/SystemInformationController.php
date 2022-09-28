<?php

namespace river\Http\Controllers\Admin\Nodes;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use river\Models\Node;
use Illuminate\Http\JsonResponse;
use river\Http\Controllers\Controller;
use river\Repositories\Wings\DaemonConfigurationRepository;

class SystemInformationController extends Controller
{
    /**
     * @var \river\Repositories\Wings\DaemonConfigurationRepository
     */
    private $repository;

    /**
     * SystemInformationController constructor.
     */
    public function __construct(DaemonConfigurationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns system information from the Daemon.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function __invoke(Request $request, Node $node)
    {
        $data = $this->repository->setNode($node)->getSystemInformation();

        return new JsonResponse([
            'version' => $data['version'] ?? '',
            'system' => [
                'type' => Str::title($data['os'] ?? 'Unknown'),
                'arch' => $data['architecture'] ?? '--',
                'release' => $data['kernel_version'] ?? '--',
                'cpus' => $data['cpu_count'] ?? 0,
            ],
        ]);
    }
}
