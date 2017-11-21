<?php

namespace App\Command;

use Base;
use Nutrition\Console\Command;
use Nutrition\Utils\FileSystem;

class CacheCommand extends Command
{
    public function clearAction(Base $app)
    {
        FileSystem::create($app['TEMP'])->removeDir();

        $this->writeln(sprintf(
            '<info>Cache cleared for environment: </> <warning>%s</>',
            $this->getOption('env')
        ));
    }

    public static function registerSelf(Base $app)
    {
        $app->route(
            'GET @cache_clear_command: /cache/clear [cli]',
            self::class . '->clearAction'
        );
    }
}
