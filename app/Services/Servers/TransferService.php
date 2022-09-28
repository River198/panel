<?php

namespace river\Services\Servers;

use river\Models\Server;
use river\Repositories\Wings\DaemonServerRepository;
use river\Contracts\Repository\ServerRepositoryInterface;

class TransferService
{
    /**
     * @var \river\Contracts\Repository\ServerRepositoryInterface
     */
    private $repository;

    /**
     * @var \river\Repositories\Wings\DaemonServerRepository
     */
    private $daemonServerRepository;

    /**
     * TransferService constructor.
     */
    public function __construct(
        DaemonServerRepository $daemonServerRepository,
        ServerRepositoryInterface $repository
    ) {
        $this->repository = $repository;
        $this->daemonServerRepository = $daemonServerRepository;
    }

    /**
     * Requests an archive from the daemon.
     *
     * @param int|\river\Models\Server $server
     *
     * @throws \Throwable
     */
    public function requestArchive(Server $server)
    {
        $this->daemonServerRepository->setServer($server)->requestArchive();
    }
}
