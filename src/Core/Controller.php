<?php

namespace App\Core;

use App\Service\BackupRestore;
use App\Service\DataValidator;
use App\Service\EntityLoader;
use App\Service\Menu;
use App\Service\ReportSetup;
use App\Service\Setting;
use Base;
use Nutrition\SQL\ConnectionBuilder;
use Nutrition\Security\Authentication;
use Nutrition\Security\Authorization;
use Nutrition\Security\UserManager;
use Nutrition\Utils\Breadcrumb;
use Nutrition\Utils\ExtendedTemplate;
use Nutrition\Utils\FlashMessage;
use Nutrition\Utils\Route;
use Nutrition\Utils\TemplateSetup;
use RuntimeException;

abstract class Controller
{
    public function __get($name)
    {
        switch ($name) {
            // case 'menu': return Menu::instance();
            case 'route': return Route::instance();
            case 'breadcrumb': return Breadcrumb::instance();
            case 'setup': return TemplateSetup::instance();
            case 'access': return Authorization::instance();
            case 'auth': return Authentication::instance();
            case 'config': return Setting::instance();
            case 'template': return ExtendedTemplate::instance();
            case 'flash': return FlashMessage::instance();
            case 'db': return ConnectionBuilder::instance();
            case 'backup': return BackupRestore::instance();
            case 'app': return Base::instance();
            case 'user': return UserManager::instance()->getUser();
            case 'userManager': return UserManager::instance();
            case 'entity': return EntityLoader::instance();
            case 'validator': return DataValidator::instance();
            case 'report': return ReportSetup::instance();
            case 'isPost': return Base::instance()->get('VERB')==='POST';
        }

        throw new RuntimeException(sprintf(
            'Method "%s" was not exists in %s',
            $name,
            static::class
        ));
    }

    public function beforeroute(Base $app, array $params)
    {
        if (
            !in_array($app['ALIAS'], ['maintenance','auth_login'])
            && $this->config->isMaintenance()
            && !$this->access->isGranted('ROLE_SUPER_ADMIN')
        ) {
            $app->reroute('maintenance');
        }

        $this->access->guard();
    }

    protected function renderPrint($file)
    {
        $this->app->set('print_content', $this->template->render($file));

        echo $this->template->render('print.html');
    }

    protected function renderHome($file)
    {
        $this->app->set('home_content', $this->template->render($file));

        echo $this->template->render('home.html');
    }

    protected function renderAuth($file)
    {
        $this->app->set('auth_content', $this->template->render($file));

        echo $this->template->render('auth.html');
    }

    protected function renderAdmin($file)
    {
        $this->app->set('admin_content', $this->template->render('admin/'.$file));

        echo $this->template->render('admin.html');
    }

    protected function render($file)
    {
        echo $this->template->render($file);
    }

    protected function json(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function notFoundIfFalse($param, $message = '')
    {
        if (!$param) {
            $this->app->error(404, $message);
        }

        return $this;
    }
}
