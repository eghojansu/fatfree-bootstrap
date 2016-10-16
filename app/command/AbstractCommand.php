<?php

namespace app\command;

use Base;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    protected $io;
    protected $input;
    protected $output;

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;

        return $this;
    }

    protected function reallyDone($message)
    {
        $this->io->success($message);
    }

    protected function info($info)
    {
        $this->output->write("<fg=yellow>$info ...</>");
    }

    protected function error($error)
    {
        $this->output->writeln("<fg=red>$error</>");
    }

    protected function done()
    {
        $this->output->writeln('<fg=green>done</>');
    }

    protected function base()
    {
        return Base::instance();
    }
}
