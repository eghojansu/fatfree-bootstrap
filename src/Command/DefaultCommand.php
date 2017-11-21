<?php

namespace App\Command;

use Base;
use Nutrition\Console\Command;
use Nutrition\Security\Security;

class DefaultCommand extends Command
{
    public function listAction(Base $app)
    {
        $this->writeln('<info>Availables commands:</>');

        $headers = ['<info>Path</>','<info>Command</>'];
        $rows = [];
        foreach ($app['ROUTES'] as $pattern => $routes) {
            if (isset($routes[Base::REQ_CLI])) {
                foreach ($routes[Base::REQ_CLI] as $route) {
                    list($handler,$ttl,$kbps,$alias)=$route;
                    $rows[] = ['<warning>'.$pattern.'</>', $alias ?: '~'];
                }
            }
        }

        $this->writeTable($headers, $rows);
    }

    public function encodePasswordAction(Base $app)
    {
        $password = $this->getOption('password');

        if ($password) {
            $this->writeln('<danger>No password encoded</>');
        } else {
            $this->writeln(Security::instance()->getPasswordEncoder()->encodePassword($password));
        }
    }

    public static function registerSelf(Base $app)
    {
        $app->route(
            'GET @list_command: / [cli]',
            self::class . '->listAction'
        );
        $app->route(
            'GET @encode_password_command: /encode [cli]',
            self::class . '->encodePasswordAction'
        );
    }
}
