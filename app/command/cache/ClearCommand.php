<?php

namespace app\command\cache;

use Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use app\command\AbstractCommand;

class ClearCommand extends AbstractCommand
{
    public function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear cache')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureIO($input, $output);

        Base::instance()->clear('CACHE');

        $this->reallyDone('Cache cleared');
    }
}
