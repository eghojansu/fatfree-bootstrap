<?php

namespace App\Command;

use App\Service\BackupRestore;
use Base;
use Nutrition\Console\Command;

class TaskCommand extends Command
{
    public function backupAction(Base $app)
    {
        $counter = BackupRestore::instance()->performAllBackupTask();

        if ($counter) {
            $this->writeln("<info>$counter task(s) successfully performed</>");
        } else {
            $this->writeln("<warning>no task performed</>");
        }
    }

    public function restoreAction(Base $app)
    {
        $counter = BackupRestore::instance()->performAllRestoreTask();

        if ($counter) {
            $this->writeln("<info>$counter task(s) successfully performed</>");
        } else {
            $this->writeln("<warning>no task performed</>");
        }
    }

    public static function registerSelf(Base $app)
    {
        $app->route(
            'GET @task_backup: /task/backup [cli]',
            self::class . '->backupAction'
        );
        $app->route(
            'GET @task_restore: /task/restore [cli]',
            self::class . '->restoreAction'
        );
    }
}
