<?php

namespace river\Http\Controllers\Admin\Nodes;

use Illuminate\Http\Request;
use river\Models\Node;
use Illuminate\Support\Collection;
use river\Models\Allocation;
use Illuminate\Contracts\View\Factory;
use river\Http\Controllers\Controller;
use river\Repositories\Eloquent\NodeRepository;
use river\Repositories\Eloquent\ServerRepository;
use river\Traits\Controllers\JavascriptInjection;
use river\Services\Helpers\SoftwareVersionService;
use river\Repositories\Eloquent\LocationRepository;
use river\Repositories\Eloquent\AllocationRepository;

class NodeViewController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \river\Repositories\Eloquent\NodeRepository
     */
    private $repository;

    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    /**
     * @var \river\Services\Helpers\SoftwareVersionService
     */
    private $versionService;

    /**
     * @var \river\Repositories\Eloquent\LocationRepository
     */
    private $locationRepository;

    /**
     * @var \river\Repositories\Eloquent\AllocationRepository
     */
    private $allocationRepository;

    /**
     * @var \river\Repositories\Eloquent\ServerRepository
     */
    private $serverRepository;

    /**
     * NodeViewController constructor.
     */
    public function __construct(
        AllocationRepository $allocationRepository,
        LocationRepository $locationRepository,
        NodeRepository $repository,
        ServerRepository $serverRepository,
        SoftwareVersionService $versionService,
        Factory $view
    ) {
        $this->repository = $repository;
        $this->view = $view;
        $this->versionService = $versionService;
        $this->locationRepository = $locationRepository;
        $this->allocationRepository = $allocationRepository;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Returns index view for a specific node on the system.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, Node $node)
    {
        $node = $this->repository->loadLocationAndServerCount($node);

        return $this->view->make('admin.nodes.view.index', [
            'node' => $node,
            'stats' => $this->repository->getUsageStats($node),
            'version' => $this->versionService,
        ]);
    }

    /**
     * Returns the settings page for a specific node.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function settings(Request $request, Node $node)
    {
        return $this->view->make('admin.nodes.view.settings', [
            'node' => $node,
            'locations' => $this->locationRepository->all(),
        ]);
    }

    /**
     * Return the node configuration page for a specific node.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function configuration(Request $request, Node $node)
    {
        return $this->view->make('admin.nodes.view.configuration', compact('node'));
    }

    /**
     * Return the node allocation management page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function allocations(Request $request, Node $node)
    {
        $node = $this->repository->loadNodeAllocations($node);

        $this->plainInject(['node' => Collection::wrap($node)->only(['id'])]);

        return $this->view->make('admin.nodes.view.allocation', [
            'node' => $node,
            'allocations' => Allocation::query()->where('node_id', $node->id)
                ->groupBy('ip')
                ->orderByRaw('INET_ATON(ip) ASC')
                ->get(['ip']),
        ]);
    }

    /**
     * Return a listing of servers that exist for this specific node.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function servers(Request $request, Node $node)
    {
        $this->plainInject([
            'node' => Collection::wrap($node->makeVisible(['daemon_token_id', 'daemon_token']))
                ->only(['scheme', 'fqdn', 'daemonListen', 'daemon_token_id', 'daemon_token']),
        ]);

        return $this->view->make('admin.nodes.view.servers', [
            'node' => $node,
            'servers' => $this->serverRepository->loadAllServersForNode($node->id, 25),
        ]);
    }
}
