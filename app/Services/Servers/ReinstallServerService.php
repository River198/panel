<?php

namespace river\Services\Servers;

use river\Models\Server;
use Illuminate\Database\ConnectionInterface;
use river\Repositories\Wings\DaemonServerRepository;

class ReinstallServerService
{
    /**
     * @var \river\Repositories\Wings\DaemonServerRepository
     */
    private $daemonServerRepository;

    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * ReinstallService constructor.
     */
    public function __construct(
        ConnectionInterface $connection,
        DaemonServerRepository $daemonServerRepository
    ) {
        $this->daemonServerRepository = $daemonServerRepository;
        $this->connection = $connection;
    }

    /**
     * Reinstall a server on the remote daemon.
     *
     * @return \river\Models\Server
     *
     * @throws \Throwable
     */
    public function handle(Server $server)
    {
        return $this->connection->transaction(function () use ($server) {
            $server->fill(['status' => Server::STATUS_INSTALLING])->save();

            $this->daemonServerRepository->setServer($server)->reinstall();

            return $server->refresh();
        });
    }
}
