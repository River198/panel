<?php

namespace river\Tests\Unit\Http\Middleware;

use river\Tests\TestCase;
use river\Tests\Traits\Http\RequestMockHelpers;
use river\Tests\Traits\Http\MocksMiddlewareClosure;
use river\Tests\Assertions\MiddlewareAttributeAssertionsTrait;

abstract class MiddlewareTestCase extends TestCase
{
    use MiddlewareAttributeAssertionsTrait;
    use MocksMiddlewareClosure;
    use RequestMockHelpers;

    /**
     * Setup tests with a mocked request object and normal attributes.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->buildRequestMock();
    }
}
