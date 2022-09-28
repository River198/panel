<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Http\Controllers\Admin;

use Illuminate\Http\Request;
use river\Models\Node;
use Illuminate\Http\Response;
use river\Models\Allocation;
use Prologue\Alerts\AlertsMessageBag;
use river\Http\Controllers\Controller;
use river\Services\Nodes\NodeUpdateService;
use Illuminate\Cache\Repository as CacheRepository;
use river\Services\Nodes\NodeCreationService;
use river\Services\Nodes\NodeDeletionService;
use river\Services\Allocations\AssignmentService;
use river\Services\Helpers\SoftwareVersionService;
use river\Http\Requests\Admin\Node\NodeFormRequest;
use river\Contracts\Repository\NodeRepositoryInterface;
use river\Contracts\Repository\ServerRepositoryInterface;
use river\Http\Requests\Admin\Node\AllocationFormRequest;
use river\Services\Allocations\AllocationDeletionService;
use river\Contracts\Repository\LocationRepositoryInterface;
use river\Contracts\Repository\AllocationRepositoryInterface;
use river\Http\Requests\Admin\Node\AllocationAliasFormRequest;

class NodesController extends Controller
{
    /**
     * @var \river\Services\Allocations\AllocationDeletionService
     */
    protected $allocationDeletionService;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \river\Contracts\Repository\AllocationRepositoryInterface
     */
    protected $allocationRepository;

    /**
     * @var \river\Services\Allocations\AssignmentService
     */
    protected $assignmentService;

    /**
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;

    /**
     * @var \river\Services\Nodes\NodeCreationService
     */
    protected $creationService;

    /**
     * @var \river\Services\Nodes\NodeDeletionService
     */
    protected $deletionService;

    /**
     * @var \river\Contracts\Repository\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var \river\Contracts\Repository\NodeRepositoryInterface
     */
    protected $repository;

    /**
     * @var \river\Contracts\Repository\ServerRepositoryInterface
     */
    protected $serverRepository;

    /**
     * @var \river\Services\Nodes\NodeUpdateService
     */
    protected $updateService;

    /**
     * @var \river\Services\Helpers\SoftwareVersionService
     */
    protected $versionService;

    /**
     * NodesController constructor.
     */
    public function __construct(
        AlertsMessageBag $alert,
        AllocationDeletionService $allocationDeletionService,
        AllocationRepositoryInterface $allocationRepository,
        AssignmentService $assignmentService,
        CacheRepository $cache,
        NodeCreationService $creationService,
        NodeDeletionService $deletionService,
        LocationRepositoryInterface $locationRepository,
        NodeRepositoryInterface $repository,
        ServerRepositoryInterface $serverRepository,
        NodeUpdateService $updateService,
        SoftwareVersionService $versionService
    ) {
        $this->alert = $alert;
        $this->allocationDeletionService = $allocationDeletionService;
        $this->allocationRepository = $allocationRepository;
        $this->assignmentService = $assignmentService;
        $this->cache = $cache;
        $this->creationService = $creationService;
        $this->deletionService = $deletionService;
        $this->locationRepository = $locationRepository;
        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
        $this->updateService = $updateService;
        $this->versionService = $versionService;
    }

    /**
     * Displays create new node page.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create()
    {
        $locations = $this->locationRepository->all();
        if (count($locations) < 1) {
            $this->alert->warning(trans('admin/node.notices.location_required'))->flash();

            return redirect()->route('admin.locations');
        }

        return view('admin.nodes.new', ['locations' => $locations]);
    }

    /**
     * Post controller to create a new node on the system.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \river\Exceptions\Model\DataValidationException
     */
    public function store(NodeFormRequest $request)
    {
        $node = $this->creationService->handle($request->normalize());
        $this->alert->info(trans('admin/node.notices.node_created'))->flash();

        return redirect()->route('admin.nodes.view.allocation', $node->id);
    }

    /**
     * Updates settings for a node.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \river\Exceptions\DisplayException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function updateSettings(NodeFormRequest $request, Node $node)
    {
        $this->updateService->handle($node, $request->normalize(), $request->input('reset_secret') === 'on');
        $this->alert->success(trans('admin/node.notices.node_updated'))->flash();

        return redirect()->route('admin.nodes.view.settings', $node->id)->withInput();
    }

    /**
     * Removes a single allocation from a node.
     *
     * @throws \river\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveSingle(int $node, Allocation $allocation): Response
    {
        $this->allocationDeletionService->handle($allocation);

        return response('', 204);
    }

    /**
     * Removes multiple individual allocations from a node.
     *
     * @throws \river\Exceptions\Service\Allocation\ServerUsingAllocationException
     */
    public function allocationRemoveMultiple(Request $request, int $node): Response
    {
        $allocations = $request->input('allocations');
        foreach ($allocations as $rawAllocation) {
            $allocation = new Allocation();
            $allocation->id = $rawAllocation['id'];
            $this->allocationRemoveSingle($node, $allocation);
        }

        return response('', 204);
    }

    /**
     * Remove all allocations for a specific IP at once on a node.
     *
     * @param int $node
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function allocationRemoveBlock(Request $request, $node)
    {
        $this->allocationRepository->deleteWhere([
            ['node_id', '=', $node],
            ['server_id', '=', null],
            ['ip', '=', $request->input('ip')],
        ]);

        $this->alert->success(trans('admin/node.notices.unallocated_deleted', ['ip' => $request->input('ip')]))
            ->flash();

        return redirect()->route('admin.nodes.view.allocation', $node);
    }

    /**
     * Sets an alias for a specific allocation on a node.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function allocationSetAlias(AllocationAliasFormRequest $request)
    {
        $this->allocationRepository->update($request->input('allocation_id'), [
            'ip_alias' => (empty($request->input('alias'))) ? null : $request->input('alias'),
        ]);

        return response('', 204);
    }

    /**
     * Creates new allocations on a node.
     *
     * @param int|\river\Models\Node $node
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \river\Exceptions\Service\Allocation\CidrOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\InvalidPortMappingException
     * @throws \river\Exceptions\Service\Allocation\PortOutOfRangeException
     * @throws \river\Exceptions\Service\Allocation\TooManyPortsInRangeException
     */
    public function createAllocation(AllocationFormRequest $request, Node $node)
    {
        $this->assignmentService->handle($node, $request->normalize());
        $this->alert->success(trans('admin/node.notices.allocations_added'))->flash();

        return redirect()->route('admin.nodes.view.allocation', $node->id);
    }

    /**
     * Deletes a node from the system.
     *
     * @param $node
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \river\Exceptions\DisplayException
     */
    public function delete($node)
    {
        $this->deletionService->handle($node);
        $this->alert->success(trans('admin/node.notices.node_deleted'))->flash();

        return redirect()->route('admin.nodes');
    }
}