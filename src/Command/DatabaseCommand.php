<?php

namespace App\Command;

use Base;
use InvalidArgumentException;
use Nutrition\Console\Command;
use Nutrition\SQL\ConnectionBuilder;

class DatabaseCommand extends Command
{
    public function createAction(Base $app)
    {
        $db = ConnectionBuilder::instance();
        $name = $db->getConfig('name');

        $sql = 'CREATE DATABASE';
        if ($this->hasOption('if-not-exists')) {
            $sql .= ' IF NOT EXISTS';
        }
        $sql .= ' '.$name;

        $result = $db->pdoWithoutDB()->exec($sql);

        if ($result) {
            $this->writeln(sprintf(
                '<info>Database "%s" has been created</>',
                $name
            ));
        } else {
            $this->writeln(sprintf(
                '<warning>Failed creating database "%s"</>',
                $name
            ));
        }
    }

    public function dropAction(Base $app)
    {
        $db = ConnectionBuilder::instance();
        $name = $db->getConfig('name');

        $sql = 'DROP DATABASE';
        if ($this->hasOption('if-exists')) {
            $sql .= ' IF EXISTS';
        }
        $sql .= ' '.$name;

        $result = $db->pdoWithoutDB()->exec($sql);

        if ($result) {
            $this->writeln(sprintf(
                '<info>Database "%s" has been dropped</>',
                $name
            ));
        } else {
            $this->writeln(sprintf(
                '<warning>Failed dropping database "%s"</>',
                $name
            ));
        }
    }

    public function importAction(Base $app, array $params)
    {
        $filesToImport = explode('/', $params['*']);
        $dir = $app['PROJECT_DIR'] . '/database';
        $filesAvailable = [
            'reset'  => $dir.'/101-reset.sql',
            'drop'   => $dir.'/100-drop.sql',
            'create' => $dir.'/300-create.sql',
            'admin'  => $dir.'/200-admin.sql',
            'setup'  => $dir.'/201-setup.sql',
            'sample'  => $dir.'/400-sample.sql',
            'relation' => $dir.'/301-relation.sql',
        ];
        $files = array_fill_keys(array_keys($filesAvailable), null);

        if ('show' === $filesToImport[0]) {
            $this->writeln('<info>Available files:</>');
            $this->writeTable(['<info>File</>', '<info>Description</>'], [
                ['<warning>reset</>', 'Reset database content'],
                ['<warning>drop</>', 'Drop schema'],
                ['<warning>create</>','Create schema'],
                ['<warning>relation</>', 'Make table relation'],
                ['<warning>admin</>', 'Insert initial admin data'],
                ['<warning>setup</>', 'Insert initial setup data'],
                ['<warning>sample</>', 'Insert sample data'],
            ]);

            return;
        }

        foreach ($filesToImport as $file) {
            if (array_key_exists($file, $filesAvailable)) {
                $files[$file] = $filesAvailable[$file];
            } else {
                $this->writeln(sprintf(
                    '<warning>Invalid file %s, pass "show" argument to see all available files</>',
                    $file
                ));

                return;
            }
        }

        $db = ConnectionBuilder::instance();
        $conn = $db->getConnection();
        $loaded = 0;
        foreach ($files as $key => $file) {
            if ($file) {
                $sql = file_get_contents($file);
                $conn->exec($sql);
                $loaded++;
            }
        }

        $this->writeln(sprintf(
            '<info>%s files has been imported</>',
            $loaded
        ));
    }

    public static function registerSelf(Base $app)
    {
        $app->route(
            'GET @db_create_command: /db/create [cli]',
            self::class . '->createAction'
        );
        $app->route(
            'GET @db_drop_command: /db/drop [cli]',
            self::class . '->dropAction'
        );
        $app->route(
            'GET @db_import_command: /db/import/* [cli]',
            self::class . '->importAction'
        );
    }

    protected function parseDescription()
    {
        # code...
    }
}
