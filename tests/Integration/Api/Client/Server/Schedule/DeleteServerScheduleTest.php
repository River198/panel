<?php

namespace river\Tests\Integration\Api\Client\Server\Schedule;

use river\Models\Task;
use Illuminate\Http\Response;
use river\Models\Schedule;
use river\Models\Permission;
use river\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DeleteServerScheduleTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that a schedule can be deleted from the system.
     *
     * @param array $permissions
     * @dataProvider permissionsDataProvider
     */
    public function testScheduleCanBeDeleted($permissions)
    {
        [$user, $server] = $this->generateTestAccount($permissions);

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);

        $this->actingAs($user)
            ->deleteJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}")
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test that no error is returned if the schedule does not exist on the system at all.
     */
    public function testNotFoundErrorIsReturnedIfScheduleDoesNotExistAtAll()
    {
        [$user, $server] = $this->generateTestAccount();

        $this->actingAs($user)
            ->deleteJson("/api/client/servers/{$server->uuid}/schedules/123456789")
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * Ensure that a schedule belonging to another server cannot be deleted and its presence is not
     * revealed to the user.
     */
    public function testNotFoundErrorIsReturnedIfScheduleDoesNotBelongToServer()
    {
        [$user, $server] = $this->generateTestAccount();
        $server2 = $this->createServerModel(['owner_id' => $user->id]);

        $schedule = Schedule::factory()->create(['server_id' => $server2->id]);

        $this->actingAs($user)
            ->deleteJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}")
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that an error is returned if the subuser does not have the required permissions to
     * delete the schedule from the server.
     */
    public function testErrorIsReturnedIfSubuserDoesNotHaveRequiredPermissions()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_UPDATE]);

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)
            ->deleteJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
    }

    public function permissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SCHEDULE_DELETE]]];
    }
}
