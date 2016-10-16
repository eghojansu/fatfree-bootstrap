<?php

namespace app\command\database;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use app\command\AbstractCommand;

class ClearCommand extends AbstractCommand
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

        $db = $this->base()->get('DB.SQL');
        $affected = 0;
        foreach ($this->tables as $table) {
            $table = $db->quotekey($table);
            $sql = "DELETE FROM $table";
            $affected += $db->exec($sql);
        }

        $this->reallyDone('Database content cleared ('.count($this->tables).' table(s) cleared, '.$affected.' record(s) affected)');
    }
}
