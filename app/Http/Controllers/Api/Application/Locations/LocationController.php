<?php

namespace river\Http\Controllers\Api\Application\Locations;

use Illuminate\Http\Response;
use river\Models\Location;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use river\Services\Locations\LocationUpdateService;
use river\Services\Locations\LocationCreationService;
use river\Services\Locations\LocationDeletionService;
use river\Contracts\Repository\LocationRepositoryInterface;
use river\Transformers\Api\Application\LocationTransformer;
use river\Http\Controllers\Api\Application\ApplicationApiController;
use river\Http\Requests\Api\Application\Locations\GetLocationRequest;
use river\Http\Requests\Api\Application\Locations\GetLocationsRequest;
use river\Http\Requests\Api\Application\Locations\StoreLocationRequest;
use river\Http\Requests\Api\Application\Locations\DeleteLocationRequest;
use river\Http\Requests\Api\Application\Locations\UpdateLocationRequest;

class LocationController extends ApplicationApiController
{
    /**
     * @var \river\Services\Locations\LocationCreationService
     */
    private $creationService;

    /**
     * @var \river\Services\Locations\LocationDeletionService
     */
    private $deletionService;

    /**
     * @var \river\Contracts\Repository\LocationRepositoryInterface
     */
    private $repository;

    /**
     * @var \river\Services\Locations\LocationUpdateService
     */
    private $updateService;

    /**
     * LocationController constructor.
     */
    public function __construct(
        LocationCreationService $creationService,
        LocationDeletionService $deletionService,
        LocationRepositoryInterface $repository,
        LocationUpdateService $updateService
    ) {
        parent::__construct();

        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->repository = $repository;
        $this->updateService = $updateService;
    }

    /**
     * Return all of the locations currently registered on the Panel.
     */
    public function index(GetLocationsRequest $request): array
    {
        $locations = QueryBuilder::for(Location::query())
            ->allowedFilters(['short', 'long'])
            ->allowedSorts(['id'])
            ->paginate($request->query('per_page') ?? 50);

        return $this->fractal->collection($locations)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Return a single location.
     */
    public function view(GetLocationRequest $request, Location $location): array
    {
        return $this->fractal->item($location)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Store a new location on the Panel and return a HTTP/201 response code with the
     * new location attached.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->creationService->handle($request->validated());

        return $this->fractal->item($location)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->addMeta([
                'resource' => route('api.application.locations.view', [
                    'location' => $location->id,
                ]),
            ])
            ->respond(201);
    }

    /**
     * Update a location on the Panel and return the updated record to the user.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateLocationRequest $request, Location $location): array
    {
        $location = $this->updateService->handle($location, $request->validated());

        return $this->fractal->item($location)
            ->transformWith($this->getTransformer(LocationTransformer::class))
            ->toArray();
    }

    /**
     * Delete a location from the Panel.
     *
     * @throws \river\Exceptions\Service\Location\HasActiveNodesException
     */
    public function delete(DeleteLocationRequest $request, Location $location): Response
    {
        $this->deletionService->handle($location);

        return response('', 204);
    }
}
