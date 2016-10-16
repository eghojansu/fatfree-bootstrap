<?php

namespace app\command\database;

use Nutrition\SQLMapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use app\command\AbstractCommand;

class SeedCommand extends AbstractCommand
{
    protected $tables = 0;
    protected $skipped = 0;
    protected $records = 0;

    public function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Seed database content')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureIO($input, $output);

        $this->reallyDone("Database seeding complete (Records: {$this->records} Tables/Skipped : {$this->tables}/{$this->skipped})");
    }

    protected function seed($table, $execute = true, $callback = null)
    {
        $tables = is_array($table)?$table:[$table];

        if (!$execute || !$callback) {
            $this->skipped += count($tables);

            return $this;
        }

        $resources = [];
        foreach ($tables as $table) {
            $resources[] = (false === strpos($table, '\\'))?new $table:new SQLMapper($table);
        }
        $this->tables += count($tables);
        $this->records += (int) call_user_func_array($callback, $resources);

        return $this;
    }
}
