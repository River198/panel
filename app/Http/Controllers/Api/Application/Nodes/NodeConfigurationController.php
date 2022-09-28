<?php

namespace river\Http\Controllers\Api\Application\Nodes;

use river\Models\Node;
use Illuminate\Http\JsonResponse;
use river\Http\Requests\Api\Application\Nodes\GetNodeRequest;
use river\Http\Controllers\Api\Application\ApplicationApiController;

class NodeConfigurationController extends ApplicationApiController
{
    /**
     * Returns the configuration information for a node. This allows for automated deployments
     * to remote machines so long as an API key is provided to the machine to make the request
     * with, and the node is known.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(GetNodeRequest $request, Node $node)
    {
        return new JsonResponse($node->getConfiguration());
    }
}
