<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use river\Models\Server;
use Illuminate\Http\JsonResponse;
use river\Facades\Activity;
use river\Repositories\Eloquent\ServerRepository;
use river\Services\Servers\ReinstallServerService;
use river\Http\Controllers\Api\Client\ClientApiController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use river\Http\Requests\Api\Client\Servers\Settings\RenameServerRequest;
use river\Http\Requests\Api\Client\Servers\Settings\SetDockerImageRequest;
use river\Http\Requests\Api\Client\Servers\Settings\ReinstallServerRequest;

class SettingsController extends ClientApiController
{
    /**
     * @var \river\Repositories\Eloquent\ServerRepository
     */
    private $repository;

    /**
     * @var \river\Services\Servers\ReinstallServerService
     */
    private $reinstallServerService;

    /**
     * SettingsController constructor.
     */
    public function __construct(
        ServerRepository $repository,
        ReinstallServerService $reinstallServerService
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->reinstallServerService = $reinstallServerService;
    }

    /**
     * Renames a server.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function rename(RenameServerRequest $request, Server $server)
    {
        $this->repository->update($server->id, [
            'name' => $request->input('name'),
        ]);

        if ($server->name !== $request->input('name')) {
            Activity::event('server:settings.rename')
                ->property(['old' => $server->name, 'new' => $request->input('name')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Reinstalls the server on the daemon.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function reinstall(ReinstallServerRequest $request, Server $server)
    {
        $this->reinstallServerService->handle($server);

        Activity::event('server:reinstall')->log();

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }

    /**
     * Changes the Docker image in use by the server.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function dockerImage(SetDockerImageRequest $request, Server $server)
    {
        if (!in_array($server->image, array_values($server->egg->docker_images))) {
            throw new BadRequestHttpException('This server\'s Docker image has been manually set by an administrator and cannot be updated.');
        }

        $original = $server->image;
        $server->forceFill(['image' => $request->input('docker_image')])->saveOrFail();

        if ($original !== $server->image) {
            Activity::event('server:startup.image')
                ->property(['old' => $original, 'new' => $request->input('docker_image')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
