<?php

namespace river\Http\Controllers\Api\Application\Servers;

use river\Models\Server;
use river\Services\Servers\BuildModificationService;
use river\Services\Servers\DetailsModificationService;
use river\Transformers\Api\Application\ServerTransformer;
use river\Http\Controllers\Api\Application\ApplicationApiController;
use river\Http\Requests\Api\Application\Servers\UpdateServerDetailsRequest;
use river\Http\Requests\Api\Application\Servers\UpdateServerBuildConfigurationRequest;

class ServerDetailsController extends ApplicationApiController
{
    /**
     * @var \river\Services\Servers\BuildModificationService
     */
    private $buildModificationService;

    /**
     * @var \river\Services\Servers\DetailsModificationService
     */
    private $detailsModificationService;

    /**
     * ServerDetailsController constructor.
     */
    public function __construct(
        BuildModificationService $buildModificationService,
        DetailsModificationService $detailsModificationService
    ) {
        parent::__construct();

        $this->buildModificationService = $buildModificationService;
        $this->detailsModificationService = $detailsModificationService;
    }

    /**
     * Update the details for a specific server.
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function details(UpdateServerDetailsRequest $request, Server $server): array
    {
        $updated = $this->detailsModificationService->returnUpdatedModel()->handle(
            $server,
            $request->validated()
        );

        return $this->fractal->item($updated)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Update the build details for a specific server.
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function build(UpdateServerBuildConfigurationRequest $request, Server $server): array
    {
        $server = $this->buildModificationService->handle($server, $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
