<?php

namespace river\Http\Controllers\Api\Remote\Servers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use river\Models\Allocation;
use Illuminate\Support\Facades\Log;
use river\Models\ServerTransfer;
use Illuminate\Database\ConnectionInterface;
use river\Http\Controllers\Controller;
use river\Services\Nodes\NodeJWTService;
use river\Repositories\Eloquent\ServerRepository;
use river\Repositories\Wings\DaemonServerRepository;
use river\Repositories\Wings\DaemonTransferRepository;
use river\Exceptions\Http\Connection\DaemonConnectionException;
use river\Services\Servers\ServerConfigurationStructureService;

class ServerTransferController extends Controller
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \river\Repositories\Eloquent\ServerRepository
     */
    private $repository;

    /**
     * @var \river\Repositories\Wings\DaemonServerRepository
     */
    private $daemonServerRepository;

    /**
     * @var \river\Repositories\Wings\DaemonTransferRepository
     */
    private $daemonTransferRepository;

    /**
     * @var \river\Services\Servers\ServerConfigurationStructureService
     */
    private $configurationStructureService;

    /**
     * @var \river\Services\Nodes\NodeJWTService
     */
    private $jwtService;

    /**
     * ServerTransferController constructor.
     */
    public function __construct(
        ConnectionInterface $connection,
        ServerRepository $repository,
        DaemonServerRepository $daemonServerRepository,
        DaemonTransferRepository $daemonTransferRepository,
        ServerConfigurationStructureService $configurationStructureService,
        NodeJWTService $jwtService
    ) {
        $this->connection = $connection;
        $this->repository = $repository;
        $this->daemonServerRepository = $daemonServerRepository;
        $this->daemonTransferRepository = $daemonTransferRepository;
        $this->configurationStructureService = $configurationStructureService;
        $this->jwtService = $jwtService;
    }

    /**
     * The daemon notifies us about the archive status.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     * @throws \Throwable
     */
    public function archive(Request $request, string $uuid)
    {
        $server = $this->repository->getByUuid($uuid);

        // Unsuspend the server and don't continue the transfer.
        if (!$request->input('successful')) {
            return $this->processFailedTransfer($server->transfer);
        }

        $this->connection->transaction(function () use ($server) {
            // This token is used by the new node the server is being transferred to. It allows
            // that node to communicate with the old node during the process to initiate the
            // actual file transfer.
            $token = $this->jwtService
                ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
                ->setSubject($server->uuid)
                ->handle($server->node, $server->uuid, 'sha256');

            // Update the archived field on the transfer to make clients connect to the websocket
            // on the new node to be able to receive transfer logs.
            $server->transfer->forceFill(['archived' => true])->saveOrFail();

            // On the daemon transfer repository, make sure to set the node after the server
            // because setServer() tells the repository to use the server's node and not the one
            // we want to specify.
            $this->daemonTransferRepository
                ->setServer($server)
                ->setNode($server->transfer->newNode)
                ->notify($server, $token);
        });

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * The daemon notifies us about a transfer failure.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function failure(string $uuid)
    {
        $server = $this->repository->getByUuid($uuid);

        return $this->processFailedTransfer($server->transfer);
    }

    /**
     * The daemon notifies us about a transfer success.
     *
     * @throws \Throwable
     */
    public function success(string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $transfer = $server->transfer;

        /** @var \river\Models\Server $server */
        $server = $this->connection->transaction(function () use ($server, $transfer) {
            $allocations = array_merge([$transfer->old_allocation], $transfer->old_additional_allocations);

            // Remove the old allocations for the server and re-assign the server to the new
            // primary allocation and node.
            Allocation::query()->whereIn('id', $allocations)->update(['server_id' => null]);
            $server->update([
                'allocation_id' => $transfer->new_allocation,
                'node_id' => $transfer->new_node,
            ]);

            $server = $server->fresh();
            $server->transfer->update(['successful' => true]);

            return $server;
        });

        // Delete the server from the old node making sure to point it to the old node so
        // that we do not delete it from the new node the server was transferred to.
        try {
            $this->daemonServerRepository
                ->setServer($server)
                ->setNode($transfer->oldNode)
                ->delete();
        } catch (DaemonConnectionException $exception) {
            Log::warning($exception, ['transfer_id' => $server->transfer->id]);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Release all of the reserved allocations for this transfer and mark it as failed in
     * the database.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    protected function processFailedTransfer(ServerTransfer $transfer)
    {
        $this->connection->transaction(function () use (&$transfer) {
            $transfer->forceFill(['successful' => false])->saveOrFail();

            $allocations = array_merge([$transfer->new_allocation], $transfer->new_additional_allocations);
            Allocation::query()->whereIn('id', $allocations)->update(['server_id' => null]);
        });

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
