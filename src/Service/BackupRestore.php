<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Setting;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\UserLog;
use App\Service\Setting as Config;
use Base;
use DB\SQL;
use Nutrition\SQL\ConnectionBuilder;
use Nutrition\Security\UserManager;
use Nutrition\Utils\CommonUtil;
use PDO;
use Prefab;

class BackupRestore extends Prefab
{
    /** @var array Table with its id */
    private $tables;

    /** @var array Unsafe tables to direct import */
    private $unsafe;


    public function __construct($value='')
    {
        $this->tables = [
            Post::tableName(),
            Setting::tableName(),
            Task::tableName(),
            User::tableName(),
            UserLog::tableName(),
        ];
        $this->unsafe = [
            Setting::tableName(),
            Task::tableName(),
            UserLog::tableName(),
        ];
    }

    public function verifyFile($realfile, $file)
    {
        $result = ['success'=>false,'message'=>'Invalid file'];
        $ftemp = @fopen($realfile, 'rb');

        if ($ftemp && '.sql' === strrchr($file, '.')) {
            $version = null;
            while (($buffer = fgets($ftemp)) !== false) {
                if (strpos($buffer, '-- @version') !== false) {
                    $version = trim(substr($buffer, 11));
                    break;
                }
            }
            fclose($ftemp);

            if ($version) {
                $minorVersion = CommonUtil::minorVersion($version);
                $minorAppVersion = CommonUtil::minorVersion(Base::instance()->get('APP_VERSION'));

                if (version_compare($minorVersion, $minorAppVersion, 'gt')) {
                    $result['message'] = 'Uploaded file version not supported';
                } else {
                    $result['message'] = 'File valid';
                    $result['success'] = true;
                }
            }
        }

        return $result;
    }

    public function registerBackup($desc = null)
    {
        $task = Task::create();

        $user = UserManager::instance()->getUser();

        $task->set('task', Task::TYPE_BACKUP);
        $task->set('description', $desc);
        $task->set('user_id', $user ? $user->id : null);
        $task->save();

        return $this;
    }

    public function registerRestore($file, $desc = null)
    {
        $app = Base::instance();

        if (!is_dir($app['RESTORE_DIR'])) {
            @mkdir($app['RESTORE_DIR']);
        }

        $basename = basename($file);
        if (rename($file, $app['RESTORE_DIR'].$basename)) {
            $task = Task::create();

            $user = UserManager::instance()->getUser();

            $task->set('task', Task::TYPE_RESTORE);
            $task->set('description', $desc);
            $task->set('file', $basename);
            $task->set('user_id', $user ? $user->id : null);
            $task->save();
        }

        return $this;
    }

    public function getUncompleteBackupTask()
    {
        return Task::create()->findone(
            ['progress < 100 and task = ?', Task::TYPE_BACKUP],
            ['order' => 'created_at']
        );
    }

    public function getUncompleteRestoreTask()
    {
        return Task::create()->findone(
            ['progress < 100 and task = ?', Task::TYPE_RESTORE],
            ['order' => 'created_at']
        );
    }

    public function performAllBackupTask()
    {
        $counter = 0;
        while ($task = $this->getUncompleteBackupTask()) {
            $this->performBackup($task);
            $counter++;
        }

        return $counter;
    }

    public function performBackup(Task $task)
    {
        set_time_limit(0);

        $eol = "\n";
        $app = Base::instance();
        $config = Config::instance();
        $conn = ConnectionBuilder::instance()->getConnection();
        $file = CommonUtil::random(8).'.sql';
        $start = microtime(true);

        if (!is_dir($app['BACKUP_DIR'])) {
            @mkdir($app['BACKUP_DIR']);
        }

        $ftemp = @fopen($app['BACKUP_DIR'].$file, 'wb');

        if (!$ftemp) {
            return false;
        }

        fwrite($ftemp, "-- @backup_file $file$eol".
                       "-- @generated ".date('c').$eol.
                       "-- @app {$config[appTitle]}$eol".
                       "-- @version {$app[APP_VERSION]}$eol$eol"
        );
        $counter = 0;
        $tableCount = count($this->tables);
        foreach ($this->tables as $table) {
            $this->buildBackupTable($ftemp, $table, $conn, $eol);
            $counter++;
            $task->set('progress', $counter/$tableCount*100);
            $task->save();
        }
        $ellapsed = (microtime(true)-$start)/60;
        fwrite($ftemp, "-- @ellapsed ".
            (
                $ellapsed>59?
                number_format($ellapsed, 2, '.', ',')." minute":
                "$ellapsed seconds"
            )
        );
        fclose($ftemp);

        $task->set('complete_at', $task->sqlTimestamp());
        $task->set('file', $file);
        $task->save();

        return true;
    }

    private function buildBackupTable($resource, $source, SQL $conn, $eol)
    {
        $pdo = $conn->pdo();
        $table = $conn->quotekey($source);
        $tableCount = (int) $pdo->query("SELECT count(*) as j FROM $table")->fetchColumn();
        $query = $pdo->query("SELECT * FROM $table");
        $prototype = "INSERT INTO $table (";
        $prototypeIncomplete = true;
        $tmp = '';
        $tableCounter = 1;
        $counter = 1;
        $max = 250;
        $prefix = in_array($source, $this->unsafe) ? "-- @unsafe " : '';

        fwrite($resource, "-- @startTable: $source$eol$eol");
        fwrite($resource, "{$prefix}SET FOREIGN_KEY_CHECKS=0;$eol".
                          "{$prefix}DELETE FROM $table;$eol$eol"
        );

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            if ($prototypeIncomplete) {
                foreach ($row as $key => $value) {
                    $prototype .= $conn->quotekey($key) . ',';
                }
                $prototype = rtrim($prototype, ',').") VALUES $eol";
                $prototypeIncomplete = false;
            }
            if (1 === $counter) {
                fwrite($resource, $prefix.$prototype);
            }
            $tmp = '';
            foreach ($row as $key => $value) {
                $tmp .= (self::quote($value) ? $conn->quote($value) : $value).',';
            }
            $tmp = '('.rtrim($tmp, ',').')';
            if ($counter === $max || $tableCounter === $tableCount) {
                $tmp .= ';';
                $counter = 1;
            } else {
                $tmp .= ',';
                $counter++;
            }
            fwrite($resource, $prefix.$tmp.$eol);
            $tableCounter++;
        }

        fwrite($resource, "$eol{$prefix}SET FOREIGN_KEY_CHECKS=1;$eol");
        fwrite($resource, "$eol-- @endTable: $table ($tableCount row(s))$eol$eol$eol");
    }

    public function performAllRestoreTask()
    {
        $counter = 0;
        while ($task = $this->getUncompleteRestoreTask()) {
            $this->performRestore($task);
            $counter++;
        }

        return $counter;
    }

    public function performRestore(Task $task)
    {
        set_time_limit(0);

        $app = Base::instance();
        $pdo = ConnectionBuilder::instance()->getConnection()->pdo();

        if (!is_dir($app['RESTORE_DIR'])) {
            @mkdir($app['RESTORE_DIR']);
        }

        $task->set('progress', 20);
        $task->save();

        $restore = @file_get_contents($app['RESTORE_DIR'].$task->file);
        if ($restore) {
            $pdo->exec($restore);
        }

        $task->set('complete_at', $task->sqlTimestamp());
        $task->set('progress', 100);
        $task->save();

        return true;
    }

    protected static function quote(&$val)
    {
        if (is_null($val)) {
            $val = 'NULL';
            return false;
        } elseif (is_bool($val)) {
            $val = (int) $val;
            return false;
        } elseif (is_numeric($val)) {
            return false;
        }

        return true;
    }
}
