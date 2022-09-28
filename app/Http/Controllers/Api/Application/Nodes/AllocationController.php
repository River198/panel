<?php

namespace river\Http\Controllers\Api\Application\Nodes;

use river\Models\Node;
use Illuminate\Http\JsonResponse;
use river\Models\Allocation;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use river\Services\Allocations\AssignmentService;
use river\Services\Allocations\AllocationDeletionService;
use river\Transformers\Api\Application\AllocationTransformer;
use river\Http\Controllers\Api\Application\ApplicationApiController;
use river\Http\Requests\Api\Application\Allocations\GetAllocationsRequest;
use river\Http\Requests\Api\Application\Allocations\StoreAllocationRequest;
use river\Http\Requests\Api\Application\Allocations\DeleteAllocationRequest;

class AllocationController extends ApplicationApiController
{
    /**
     * @var \river\Services\Allocations\AssignmentService
     */
    private $assignmentService;

    /**
     * @var \river\Services\Allocations\AllocationDeletionService
     */
    private $deletionService;

    /**
     * AllocationController constructor.
     */
    public function __construct(
        AssignmentService $assignmentService,
        AllocationDeletionService $deletionService
    ) {
        parent::__construct();

        $this->assignmentService = $assignmentService;
        $this->deletionService = $deletionService;
    }

    /**
     * Return all of the allocations that exist for a given node.
     */
    public function index(GetAllocationsRequest $request, Node $node): array
    {
        $allocations = QueryBuilder::for($node->allocations())
            ->allowedFilters([
                AllowedFilter::exact('ip'),
                AllowedFilter::exact('port'),
                'ip_alias',
                AllowedFilter::callback('server_id', function (Builder $builder, $value) {
                    if (empty($value) || is_bool($value) || !ctype_digit((string) $value)) {
                        return $builder->whereNull('server_id');
                    }

                    return $builder->where('server_id', $value);
                }),
            ])
            ->paginate($request->query('per_page') ?? 50);

        return $this->fractal->collection($allocations)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Store new allocations for a given node.
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \river\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function store(StoreAllocationRequest $request, Node $node): JsonResponse
    {
        $this->assignmentService->handle($node, $request->validated());

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Delete a specific allocation from the Panel.
     *
     * @throws \river\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function delete(DeleteAllocationRequest $request, Node $node, Allocation $allocation): JsonResponse
    {
        $this->deletionService->handle($allocation);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
