<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use App\Service\BackupRestore;
use Base;
use Nutrition\Utils\CommonUtil;
use Web;

class DatabaseController extends Controller
{
    public function __construct()
    {
        $this->breadcrumb->add('db', null, 'Manage Database');
        $this->setup->prefixTitle('Manage Database');
    }

    public function indexAction(Base $app)
    {
        $db = $this->db;
        $app['healthy'] = $db->isHealthy();
        $app['tables'] = $db->getTables();
        $app['db_size'] = $db->getSize();
        $app['history'] = $this->entity->task->getBackupRestoreHistory();

        $this->renderAdmin('db/index.html');
    }

    public function repairAction(Base $app)
    {
        $db = $this->db;
        $tables = $db->getUnhealthyTable();
        $tableCount = count($tables);
        $result = $db->repairTables($tables);

        if ($result) {
            $this->flash->add('success', "$tableCount table(s) has been repaired");
        } else {
            $this->flash->add('warning', 'No table repaired');
        }

        $app->reroute('db');
    }

    public function restoreAction(Base $app)
    {
        $this->validator->handle('confirm', function($app) {
            foreach ($app['SESSION.restore']?:[] as $file => $restore) {
                if ($restore) {
                    $this->backup->registerRestore($file);
                }
            }
            $this->flash->add('success', 'Restore sudah dijadwalkan');

            $app->reroute('db');
        });

        $this->renderAdmin('db/restore.html');
    }

    public function restoreTaskAction(Base $app, array $params)
    {
        $task = $this->entity->task->findOneById($params['task']);
        $this->notFoundIfFalse($task);

        $performed = false;
        if ($task->isRestore() && !$task->isComplete()) {
            $performed = BackupRestore::instance()->performRestore($task);
        }

        if ($performed) {
            $this->flash->add('success', 'Restore successfully performed');
        } else {
            $this->flash->add('warning', 'No restore performed');
        }

        $app->reroute('db');
    }

    public function backupAction(Base $app)
    {
        $this->validator->handle('confirm', function($app) {
            $this->backup->registerBackup();
            $this->flash->add('success', 'Backup sudah dijadwalkan');

            $app->reroute('db');
        });

        $this->renderAdmin('db/backup.html');
    }

    public function backupTaskAction(Base $app, array $params)
    {
        $task = $this->entity->task->findOneById($params['task']);
        $this->notFoundIfFalse($task);

        $performed = false;
        if ($task->isBackup() && !$task->isComplete()) {
            $performed = BackupRestore::instance()->performBackup($task);
        }

        if ($performed) {
            $this->flash->add('success', 'Backup successfully performed');
        } else {
            $this->flash->add('warning', 'No backup performed');
        }

        $app->reroute('db');
    }

    public function downloadAction(Base $app)
    {
        $file = $app['GET.file'];
        $path = $app['BACKUP_DIR'].$file;

        if (!$file || !file_exists($path)) {
            $app->error(404);
        }

        Web::instance()->send($path);
    }

    public function uploadAction(Base $app)
    {
        $result = null;
        $files = Web::instance()->receive(function($file) use (&$result) {
            $result = $this->backup->verifyFile($file['tmp_name'], $file['name']);

            return $result['success'];
        }, true, function($fileBaseName) {
            return CommonUtil::random(8).strrchr($fileBaseName, '.');
        });
        $app['SESSION.restore'] = null;
        if ($result['success']) {
            $app['SESSION.restore'] = $files;
        }

        $this->json($result);
    }
}
