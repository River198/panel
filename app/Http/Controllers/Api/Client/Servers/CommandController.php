<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use river\Facades\Activity;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use river\Repositories\Wings\DaemonCommandRepository;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Http\Requests\Api\Client\Servers\SendCommandRequest;
use river\Exceptions\Http\Connection\DaemonConnectionException;

class CommandController extends ClientApiController
{
    /**
     * @var \river\Repositories\Wings\DaemonCommandRepository
     */
    private $repository;

    /**
     * CommandController constructor.
     */
    public function __construct(DaemonCommandRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Send a command to a running server.
     *
     * @throws \river\Exceptions\Http\Connection\DaemonConnectionException
     */
    public function index(SendCommandRequest $request, Server $server): Response
    {
        try {
            $this->repository->setServer($server)->send($request->input('command'));
        } catch (DaemonConnectionException $exception) {
            $previous = $exception->getPrevious();

            if ($previous instanceof BadResponseException) {
                if (
                    $previous->getResponse() instanceof ResponseInterface
                    && $previous->getResponse()->getStatusCode() === Response::HTTP_BAD_GATEWAY
                ) {
                    throw new HttpException(Response::HTTP_BAD_GATEWAY, 'Server must be online in order to send commands.', $exception);
                }
            }

            throw $exception;
        }

        Activity::event('server:console.command')->property('command', $request->input('command'))->log();

        return $this->returnNoContent();
    }
}
