<?php

namespace river\Http\Controllers\Api\Client\Servers;

use river\Models\Server;
use river\Facades\Activity;
use river\Services\Servers\StartupCommandService;
use river\Services\Servers\VariableValidatorService;
use river\Repositories\Eloquent\ServerVariableRepository;
use river\Transformers\Api\Client\EggVariableTransformer;
use river\Http\Controllers\Api\Client\ClientApiController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use river\Http\Requests\Api\Client\Servers\Startup\GetStartupRequest;
use river\Http\Requests\Api\Client\Servers\Startup\UpdateStartupVariableRequest;

class StartupController extends ClientApiController
{
    /**
     * @var \river\Services\Servers\VariableValidatorService
     */
    private $service;

    /**
     * @var \river\Repositories\Eloquent\ServerVariableRepository
     */
    private $repository;

    /**
     * @var \river\Services\Servers\StartupCommandService
     */
    private $startupCommandService;

    /**
     * StartupController constructor.
     */
    public function __construct(VariableValidatorService $service, StartupCommandService $startupCommandService, ServerVariableRepository $repository)
    {
        parent::__construct();

        $this->service = $service;
        $this->repository = $repository;
        $this->startupCommandService = $startupCommandService;
    }

    /**
     * Returns the startup information for the server including all of the variables.
     *
     * @return array
     */
    public function index(GetStartupRequest $request, Server $server)
    {
        $startup = $this->startupCommandService->handle($server, false);

        return $this->fractal->collection(
            $server->variables()->where('user_viewable', true)->get()
        )
            ->transformWith($this->getTransformer(EggVariableTransformer::class))
            ->addMeta([
                'startup_command' => $startup,
                'docker_images' => $server->egg->docker_images,
                'raw_startup_command' => $server->startup,
            ])
            ->toArray();
    }

    /**
     * Updates a single variable for a server.
     *
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateStartupVariableRequest $request, Server $server)
    {
        /** @var \river\Models\EggVariable $variable */
        $variable = $server->variables()->where('env_variable', $request->input('key'))->first();
        $original = $variable->server_value;

        if (is_null($variable) || !$variable->user_viewable) {
            throw new BadRequestHttpException('The environment variable you are trying to edit does not exist.');
        } elseif (!$variable->user_editable) {
            throw new BadRequestHttpException('The environment variable you are trying to edit is read-only.');
        }

        // Revalidate the variable value using the egg variable specific validation rules for it.
        $this->validate($request, ['value' => $variable->rules]);

        $this->repository->updateOrCreate([
            'server_id' => $server->id,
            'variable_id' => $variable->id,
        ], [
            'variable_value' => $request->input('value') ?? '',
        ]);

        $variable = $variable->refresh();
        $variable->server_value = $request->input('value');

        $startup = $this->startupCommandService->handle($server, false);

        if ($variable->env_variable !== $request->input('value')) {
            Activity::event('server:startup.edit')
                ->subject($variable)
                ->property([
                    'variable' => $variable->env_variable,
                    'old' => $original,
                    'new' => $request->input('value'),
                ])
                ->log();
        }

        return $this->fractal->item($variable)
            ->transformWith($this->getTransformer(EggVariableTransformer::class))
            ->addMeta([
                'startup_command' => $startup,
                'raw_startup_command' => $server->startup,
            ])
            ->toArray();
    }
}
