<?php

namespace App;

use App\Command\CacheCommand;
use App\Command\DatabaseCommand;
use App\Command\DefaultCommand;
use App\Command\TaskCommand;
use App\Core\SessionHandler;
use App\Service\Menu;
use App\Service\Setting;
use App\Utils\Utils;
use Base;
use Carbon\Carbon;
use Nutrition\App;
use Nutrition\Security\UserManager;
use Nutrition\Utils\ExtendedTemplate;

class Kernel
{
    protected $env;


    public function __construct($env = 'prod')
    {
        $this->env = $env;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }

    public function loadConfiguration(Base $app)
    {
        $projectDir = $this->getProjectDir();
        $app['PROJECT_DIR'] = $projectDir;
        $app['APP_VERSION'] = @file_get_contents($projectDir.'/VERSION') ?: 'v0.1.0';

        $app->config($projectDir.'/.env');
        $app->config($projectDir.'/config/config_'.$this->env.'.ini', true);

        return $this;
    }

    public function addNutrition()
    {
        App::instance()->registerErrorHandler();

        return $this;
    }

    public function registerTemplateFilters()
    {
        $template = ExtendedTemplate::instance();
        $template->filter('config', Setting::class . '::instance()->get');
        $template->filter('menu_get', Menu::class . '::instance()->getMenu');
        $template->filter('menu_active', Menu::class . '::instance()->isActive');
        $template->filter('menu_set', Menu::class . '::instance()->setCurrent');
        $template->filter('user_prop', UserManager::class . '::instance()->getUser()->get');
        $template->filter('welcome_time', Utils::class . '::welcomeTime');
        $template->filter('error_set', Utils::class . '::violationSet');
        $template->filter('error_has', Utils::class . '::violationHasError');
        $template->filter('error_get', Utils::class . '::violationWriteError');

        return $this;
    }

    public function registerCommands(Base $app)
    {
        DefaultCommand::registerSelf($app);
        CacheCommand::registerSelf($app);
        DatabaseCommand::registerSelf($app);
        TaskCommand::registerSelf($app);

        return $this;
    }

    public function registerSession()
    {
        $app = Base::instance();

        session_set_save_handler(new SessionHandler(
            $app->ip(),
            $app->agent()
        ), true);
        $app['SESSION.check_time'] = time();
    }
}
