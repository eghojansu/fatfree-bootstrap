<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class UserController extends Controller
{
    public function __construct()
    {
        $this->breadcrumb->add('user', null, 'Data User');
        $this->setup->prefixTitle('Data User');
    }

    public function indexAction(Base $app)
    {
        $app['pagination'] = $this->entity->user->findAll();

        $this->renderAdmin('user/index.html');
    }

    public function createAction(Base $app)
    {
        $user = $this->entity->user;
        $this->validator->handle('user', function($app, $data) use ($user) {
            $user->copyfrom($data);
            $user->save();
            $this->flash->add('success', 'Data sudah disimpan');

            $app->reroute('user');
        }, null, $user);
        $app['user'] = $user;
        $app['blockOptions'] = $this->validator->optionOnOff();
        $app['title'] = 'Create';

        $this->renderAdmin('user/form.html');
    }

    public function updateAction(Base $app, array $params)
    {
        $user = $this->entity->user->findUser($params['user']);
        $this->notFoundIfFalse($user)->validator->handle('user', function($app, $data) use ($user) {
            $user->copyfrom($data);
            $user->save();
            $this->flash->add('success', 'Data sudah disimpan');

            $app->reroute('user');
        }, null, $user);
        $app['user'] = $user;
        $app['blockOptions'] = $this->validator->optionOnOff();
        $app['title'] = 'Update';

        $this->renderAdmin('user/form.html');
    }

    public function deleteAction(Base $app, array $params)
    {
        $user = $this->entity->user->findUser($params['user']);
        $this->notFoundIfFalse($user)->validator->handle('confirm', function($app) use ($user) {
            $user->erase();
            $this->flash->add('info', 'Data sudah dihapus');

            $app->reroute('user');
        });
        $app['user'] = $user;

        $this->renderAdmin('user/delete.html');
    }
}
