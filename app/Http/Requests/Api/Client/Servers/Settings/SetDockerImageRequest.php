<?php

namespace river\Http\Requests\Api\Client\Servers\Settings;

use Webmozart\Assert\Assert;
use river\Models\Server;
use Illuminate\Validation\Rule;
use river\Models\Permission;
use river\Contracts\Http\ClientPermissionsRequest;
use river\Http\Requests\Api\Client\ClientApiRequest;

class SetDockerImageRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_STARTUP_DOCKER_IMAGE;
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        /** @var \river\Models\Server $server */
        $server = $this->route()->parameter('server');

        Assert::isInstanceOf($server, Server::class);

        return [
            'docker_image' => ['required', 'string', Rule::in(array_values($server->egg->docker_images))],
        ];
    }
}
