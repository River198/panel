<?php

namespace river\Contracts\Repository;

use river\Models\Task;

interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a task and the server relationship for that task.
     *
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function getTaskForJobProcess(int $id): Task;

    /**
     * Returns the next task in a schedule.
     *
     * @return \river\Models\Task|null
     */
    public function getNextTask(int $schedule, int $index);
}
