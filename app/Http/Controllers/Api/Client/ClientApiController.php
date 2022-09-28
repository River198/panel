<?php

namespace river\Http\Controllers\Api\Client;

use Webmozart\Assert\Assert;
use river\Transformers\Api\Client\BaseClientTransformer;
use river\Http\Controllers\Api\Application\ApplicationApiController;

abstract class ClientApiController extends ApplicationApiController
{
    /**
     * Returns only the includes which are valid for the given transformer.
     *
     * @return string[]
     */
    protected function getIncludesForTransformer(BaseClientTransformer $transformer, array $merge = [])
    {
        $filtered = array_filter($this->parseIncludes(), function ($datum) use ($transformer) {
            return in_array($datum, $transformer->getAvailableIncludes());
        });

        return array_merge($filtered, $merge);
    }

    /**
     * Returns the parsed includes for this request.
     *
     * @return string[]
     */
    protected function parseIncludes()
    {
        $includes = $this->request->query('include') ?? [];

        if (!is_string($includes)) {
            return $includes;
        }

        return array_map(function ($item) {
            return trim($item);
        }, explode(',', $includes));
    }

    /**
     * Return an instance of an application transformer.
     *
     * @template T of \river\Transformers\Api\Client\BaseClientTransformer
     *
     * @param class-string<T> $abstract
     *
     * @return T
     *
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpDocSignatureInspection
     */
    public function getTransformer(string $abstract)
    {
        Assert::subclassOf($abstract, BaseClientTransformer::class);

        return $abstract::fromRequest($this->request);
    }
}
