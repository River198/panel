<?php

namespace river\Tests\Integration;

use Carbon\CarbonImmutable;
use river\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use river\Events\ActivityLogged;
use river\Tests\Assertions\AssertsActivityLogged;
use river\Tests\Traits\Integration\CreatesTestModels;
use river\Transformers\Api\Application\BaseTransformer;

abstract class IntegrationTestCase extends TestCase
{
    use CreatesTestModels;
    use AssertsActivityLogged;

    protected array $connectionsToTransact = ['mysql'];

    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Event::fake(ActivityLogged::class);
    }

    /**
     * Return an ISO-8601 formatted timestamp to use in the API response.
     */
    protected function formatTimestamp(string $timestamp): string
    {
        return CarbonImmutable::createFromFormat(CarbonImmutable::DEFAULT_TO_STRING_FORMAT, $timestamp)
            ->setTimezone(BaseTransformer::RESPONSE_TIMEZONE)
            ->toIso8601String();
    }
}
