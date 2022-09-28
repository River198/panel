<?php

namespace river\Tests\Integration\Services\Backups;

use Mockery;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use river\Models\Backup;
use GuzzleHttp\Exception\ClientException;
use river\Extensions\Backups\BackupManager;
use river\Services\Backups\DeleteBackupService;
use river\Tests\Integration\IntegrationTestCase;
use river\Repositories\Wings\DaemonBackupRepository;
use river\Exceptions\Service\Backup\BackupLockedException;
use river\Exceptions\Http\Connection\DaemonConnectionException;

class DeleteBackupServiceTest extends IntegrationTestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(DaemonBackupRepository::class);

        $this->app->instance(DaemonBackupRepository::class, $this->repository);
    }

    public function testLockedBackupCannotBeDeleted()
    {
        $server = $this->createServerModel();
        $backup = Backup::factory()->create([
            'server_id' => $server->id,
            'is_locked' => true,
        ]);

        $this->expectException(BackupLockedException::class);

        $this->app->make(DeleteBackupService::class)->handle($backup);
    }

    public function testFailedBackupThatIsLockedCanBeDeleted()
    {
        $server = $this->createServerModel();
        $backup = Backup::factory()->create([
            'server_id' => $server->id,
            'is_locked' => true,
            'is_successful' => false,
        ]);

        $this->repository->expects('setServer->delete')->with($backup)->andReturn(
            new Response()
        );

        $this->app->make(DeleteBackupService::class)->handle($backup);

        $backup->refresh();

        $this->assertNotNull($backup->deleted_at);
    }

    public function testExceptionThrownDueToMissingBackupIsIgnored()
    {
        $server = $this->createServerModel();
        $backup = Backup::factory()->create(['server_id' => $server->id]);

        $this->repository->expects('setServer->delete')->with($backup)->andThrow(
            new DaemonConnectionException(
                new ClientException('', new Request('DELETE', '/'), new Response(404))
            )
        );

        $this->app->make(DeleteBackupService::class)->handle($backup);

        $backup->refresh();

        $this->assertNotNull($backup->deleted_at);
    }

    public function testExceptionIsThrownIfNot404()
    {
        $server = $this->createServerModel();
        $backup = Backup::factory()->create(['server_id' => $server->id]);

        $this->repository->expects('setServer->delete')->with($backup)->andThrow(
            new DaemonConnectionException(
                new ClientException('', new Request('DELETE', '/'), new Response(500))
            )
        );

        $this->expectException(DaemonConnectionException::class);

        $this->app->make(DeleteBackupService::class)->handle($backup);

        $backup->refresh();

        $this->assertNull($backup->deleted_at);
    }

    public function testS3ObjectCanBeDeleted()
    {
        $server = $this->createServerModel();
        $backup = Backup::factory()->create([
            'disk' => Backup::ADAPTER_AWS_S3,
            'server_id' => $server->id,
        ]);

        $manager = $this->mock(BackupManager::class);
        $manager->expects('getBucket')->andReturns('foobar');
        $manager->expects('adapter')->with(Backup::ADAPTER_AWS_S3)->andReturnSelf();
        $manager->expects('getClient->deleteObject')->with([
            'Bucket' => 'foobar',
            'Key' => sprintf('%s/%s.tar.gz', $server->uuid, $backup->uuid),
        ]);

        $this->app->make(DeleteBackupService::class)->handle($backup);

        $backup->refresh();

        $this->assertNotNull($backup->deleted_at);
    }
}
