<?php

namespace river\Transformers\Api\Client;

use river\Models\Egg;

class EggTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Egg::RESOURCE_NAME;
    }

    /**
     * @return array
     */
    public function transform(Egg $egg)
    {
        return [
            'uuid' => $egg->uuid,
            'name' => $egg->name,
        ];
    }
}
