<?php

namespace river\Http\Controllers\Api\Application\Nests;

use river\Models\Nest;
use river\Contracts\Repository\NestRepositoryInterface;
use river\Transformers\Api\Application\NestTransformer;
use river\Http\Requests\Api\Application\Nests\GetNestsRequest;
use river\Http\Controllers\Api\Application\ApplicationApiController;

class NestController extends ApplicationApiController
{
    /**
     * @var \river\Contracts\Repository\NestRepositoryInterface
     */
    private $repository;

    /**
     * NestController constructor.
     */
    public function __construct(NestRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Return all Nests that exist on the Panel.
     */
    public function index(GetNestsRequest $request): array
    {
        $nests = $this->repository->paginated($request->query('per_page') ?? 50);

        return $this->fractal->collection($nests)
            ->transformWith($this->getTransformer(NestTransformer::class))
            ->toArray();
    }

    /**
     * Return information about a single Nest model.
     */
    public function view(GetNestsRequest $request, Nest $nest): array
    {
        return $this->fractal->item($nest)
            ->transformWith($this->getTransformer(NestTransformer::class))
            ->toArray();
    }
}
