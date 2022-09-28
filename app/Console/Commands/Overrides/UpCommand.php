<?php

namespace river\Console\Commands\Overrides;

use river\Console\RequiresDatabaseMigrations;
use Illuminate\Foundation\Console\UpCommand as BaseUpCommand;

class UpCommand extends BaseUpCommand
{
    use RequiresDatabaseMigrations;

    /**
     * Block someone from running this up command if they have not completed
     * the migration process.
     */
    public function handle()
    {
        if (!$this->hasCompletedMigrations()) {
            $this->showMigrationWarning();

            return;
        }

        parent::handle();
    }
}
