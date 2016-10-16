<?php

namespace app\command\database;

use Nutrition\SQLMapper;
use Nutrition\SQLTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use app\command\AbstractCommand;

class ImportCommand extends AbstractCommand
{
    protected $schemas = [
    ];
    protected $dir;

    public function configure()
    {
        $this->dir = $this->base()->get('APPDIR').'schema/';

        $this
            ->setName('db:import')
            ->setDescription('Import database schema')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureIO($input, $output);

        $sqlTool = new SQLTool($this->base()->get('DB.SQL'));

        foreach ($this->schemas as $schema) {
            $sqlTool->import($this->dir.$schema);
        }

        $this->reallyDone('Imports complete ('.count($this->schemas).' schema(s) imported)');
    }
}
