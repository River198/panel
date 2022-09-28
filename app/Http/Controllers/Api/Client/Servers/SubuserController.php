<?php

namespace river\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use river\Models\Server;
use Illuminate\Http\JsonResponse;
use river\Facades\Activity;
use river\Models\Permission;
use Illuminate\Support\Facades\Log;
use river\Repositories\Eloquent\SubuserRepository;
use river\Services\Subusers\SubuserCreationService;
use river\Repositories\Wings\DaemonServerRepository;
use river\Transformers\Api\Client\SubuserTransformer;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Exceptions\Http\Connection\DaemonConnectionException;
use river\Http\Requests\Api\Client\Servers\Subusers\GetSubuserRequest;
use river\Http\Requests\Api\Client\Servers\Subusers\StoreSubuserRequest;
use river\Http\Requests\Api\Client\Servers\Subusers\DeleteSubuserRequest;
use river\Http\Requests\Api\Client\Servers\Subusers\UpdateSubuserRequest;

class SubuserController extends ClientApiController
{
    /**
     * @var \river\Repositories\Eloquent\SubuserRepository
     */
    private $repository;

    /**
     * @var \river\Services\Subusers\SubuserCreationService
     */
    private $creationService;

    /**
     * @var \river\Repositories\Wings\DaemonServerRepository
     */
    private $serverRepository;

    /**
     * SubuserController constructor.
     */
    public function __construct(
        SubuserRepository $repository,
        SubuserCreationService $creationService,
        DaemonServerRepository $serverRepository
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->creationService = $creationService;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Return the users associated with this server instance.
     *
     * @return array
     */
    public function index(GetSubuserRequest $request, Server $server)
    {
        return $this->fractal->collection($server->subusers)
            ->transformWith($this->getTransformer(SubuserTransformer::class))
            ->toArray();
    }

    /**
     * Returns a single subuser associated with this server instance.
     *
     * @return array
     */
    public function view(GetSubuserRequest $request)
    {
        $subuser = $request->attributes->get('subuser');

        return $this->fractal->item($subuser)
            ->transformWith($this->getTransformer(SubuserTransformer::class))
            ->toArray();
    }

    /**
     * Create a new subuser for the given server.
     *
     * @return array
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Service\Subuser\ServerSubuserExistsException
     * @throws \river\Exceptions\Service\Subuser\UserIsServerOwnerException
     * @throws \Throwable
     */
    public function store(StoreSubuserRequest $request, Server $server)
    {
        $response = $this->creationService->handle(
            $server,
            $request->input('email'),
            $this->getDefaultPermissions($request)
        );

        Activity::event('server:subuser.create')
            ->subject($response->user)
            ->property(['email' => $request->input('email'), 'permissions' => $this->getDefaultPermissions($request)])
            ->log();

        return $this->fractal->item($response)
            ->transformWith($this->getTransformer(SubuserTransformer::class))
            ->toArray();
    }

    /**
     * Update a given subuser in the system for the server.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateSubuserRequest $request, Server $server): array
    {
        /** @var \river\Models\Subuser $subuser */
        $subuser = $request->attributes->get('subuser');

        $permissions = $this->getDefaultPermissions($request);
        $current = $subuser->permissions;

        sort($permissions);
        sort($current);

        $log = Activity::event('server:subuser.update')
            ->subject($subuser->user)
            ->property([
                'email' => $subuser->user->email,
                'old' => $current,
                'new' => $permissions,
                'revoked' => true,
            ]);

        // Only update the database and hit up the Wings instance to invalidate JTI's if the permissions
        // have actually changed for the user.
        if ($permissions !== $current) {
            $log->transaction(function ($instance) use ($request, $subuser, $server) {
                $this->repository->update($subuser->id, [
                    'permissions' => $this->getDefaultPermissions($request),
                ]);

                try {
                    $this->serverRepository->setServer($server)->revokeUserJTI($subuser->user_id);
                } catch (DaemonConnectionException $exception) {
                    // Don't block this request if we can't connect to the Wings instance. Chances are it is
                    // offline in this event and the token will be invalid anyways once Wings boots back.
                    Log::warning($exception, ['user_id' => $subuser->user_id, 'server_id' => $server->id]);

                    $instance->property('revoked', false);
                }
            });
        }

        $log->reset();

        return $this->fractal->item($subuser->refresh())
            ->transformWith($this->getTransformer(SubuserTransformer::class))
            ->toArray();
    }

    /**
     * Removes a subusers from a server's assignment.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteSubuserRequest $request, Server $server)
    {
        /** @var \river\Models\Subuser $subuser */
        $subuser = $request->attributes->get('subuser');

        $log = Activity::event('server:subuser.delete')
            ->subject($subuser->user)
            ->property('email', $subuser->user->email)
            ->property('revoked', true);

        $log->transaction(function ($instance) use ($server, $subuser) {
            $subuser->delete();

            try {
                $this->serverRepository->setServer($server)->revokeUserJTI($subuser->user_id);
            } catch (DaemonConnectionException $exception) {
                // Don't block this request if we can't connect to the Wings instance.
                Log::warning($exception, ['user_id' => $subuser->user_id, 'server_id' => $server->id]);

                $instance->property('revoked', false);
            }
        });

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Returns the default permissions for all subusers to ensure none are ever removed wrongly.
     */
    protected function getDefaultPermissions(Request $request): array
    {
        return array_unique(array_merge($request->input('permissions') ?? [], [Permission::ACTION_WEBSOCKET_CONNECT]));
    }
}
