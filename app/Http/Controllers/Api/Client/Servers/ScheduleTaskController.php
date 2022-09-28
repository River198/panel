<?php

namespace river\Http\Controllers\Api\Client\Servers;

use river\Models\Task;
use Illuminate\Http\Response;
use river\Models\Server;
use river\Models\Schedule;
use Illuminate\Http\JsonResponse;
use river\Facades\Activity;
use river\Models\Permission;
use river\Repositories\Eloquent\TaskRepository;
use river\Exceptions\Http\HttpForbiddenException;
use river\Transformers\Api\Client\TaskTransformer;
use river\Http\Requests\Api\Client\ClientApiRequest;
use river\Http\Controllers\Api\Client\ClientApiController;
use river\Exceptions\Service\ServiceLimitExceededException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use river\Http\Requests\Api\Client\Servers\Schedules\StoreTaskRequest;

class ScheduleTaskController extends ClientApiController
{
    /**
     * @var \river\Repositories\Eloquent\TaskRepository
     */
    private $repository;

    /**
     * ScheduleTaskController constructor.
     */
    public function __construct(TaskRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Create a new task for a given schedule and store it in the database.
     *
     * @return array
     *
     * @throws \river\Exceptions\Model\HttpForbiddenException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Service\ServiceLimitExceededException
     */
    public function store(StoreTaskRequest $request, Server $server, Schedule $schedule)
    {
        $limit = config('river.client_features.schedules.per_schedule_task_limit', 10);
        if ($schedule->tasks()->count() >= $limit) {
            throw new ServiceLimitExceededException("Schedules may not have more than {$limit} tasks associated with them. Creating this task would put this schedule over the limit.");
        }

        if ($server->backup_limit === 0 && $request->action === 'backup') {
            throw new HttpForbiddenException("A backup task cannot be created when the server's backup limit is set to 0.");
        }

        /** @var \river\Models\Task|null $lastTask */
        $lastTask = $schedule->tasks()->orderByDesc('sequence_id')->first();

        /** @var \river\Models\Task $task */
        $task = $this->repository->create([
            'schedule_id' => $schedule->id,
            'sequence_id' => ($lastTask->sequence_id ?? 0) + 1,
            'action' => $request->input('action'),
            'payload' => $request->input('payload') ?? '',
            'time_offset' => $request->input('time_offset'),
            'continue_on_failure' => (bool) $request->input('continue_on_failure'),
        ]);

        Activity::event('server:task.create')
            ->subject($schedule, $task)
            ->property(['name' => $schedule->name, 'action' => $task->action, 'payload' => $task->payload])
            ->log();

        return $this->fractal->item($task)
            ->transformWith($this->getTransformer(TaskTransformer::class))
            ->toArray();
    }

    /**
     * Updates a given task for a server.
     *
     * @return array
     *
     * @throws \river\Exceptions\Model\HttpForbiddenException
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function update(StoreTaskRequest $request, Server $server, Schedule $schedule, Task $task)
    {
        if ($schedule->id !== $task->schedule_id || $server->id !== $schedule->server_id) {
            throw new NotFoundHttpException();
        }

        if ($server->backup_limit === 0 && $request->action === 'backup') {
            throw new HttpForbiddenException("A backup task cannot be created when the server's backup limit is set to 0.");
        }

        $this->repository->update($task->id, [
            'action' => $request->input('action'),
            'payload' => $request->input('payload') ?? '',
            'time_offset' => $request->input('time_offset'),
            'continue_on_failure' => (bool) $request->input('continue_on_failure'),
        ]);

        Activity::event('server:task.update')
            ->subject($schedule, $task)
            ->property(['name' => $schedule->name, 'action' => $task->action, 'payload' => $task->payload])
            ->log();

        return $this->fractal->item($task->refresh())
            ->transformWith($this->getTransformer(TaskTransformer::class))
            ->toArray();
    }

    /**
     * Delete a given task for a schedule. If there are subsequent tasks stored in the database
     * for this schedule their sequence IDs are decremented properly.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function delete(ClientApiRequest $request, Server $server, Schedule $schedule, Task $task)
    {
        if ($task->schedule_id !== $schedule->id || $schedule->server_id !== $server->id) {
            throw new NotFoundHttpException();
        }

        if (!$request->user()->can(Permission::ACTION_SCHEDULE_UPDATE, $server)) {
            throw new HttpForbiddenException('You do not have permission to perform this action.');
        }

        $schedule->tasks()->where('sequence_id', '>', $task->sequence_id)->update([
            'sequence_id' => $schedule->tasks()->getConnection()->raw('(sequence_id - 1)'),
        ]);

        $task->delete();

        Activity::event('server:task.delete')->subject($schedule, $task)->property('name', $schedule->name)->log();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
