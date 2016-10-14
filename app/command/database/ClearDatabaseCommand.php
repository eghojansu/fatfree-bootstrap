<?php

namespace app\command\database;

use Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use app\command\AbstractCommand;

class ClearDatabaseCommand extends AbstractCommand
{
    protected $tables = [
    ];

    public function configure()
    {
        $this
            ->setName('db:clear')
            ->setDescription('Clear database content')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureIO($input, $output);

        $db = Base::instance()->get('DB.SQL');
        foreach ($this->tables as $table) {
            $table = $db->quote($table);
            $sql = "DELETE FROM $table WHERE 1; ALTER TABLE $table AUTO_INCREMENT = 1";
            $db->pdo()->exec($sql);
        }

        $this->reallyDone('Database content cleared');
    }
}
