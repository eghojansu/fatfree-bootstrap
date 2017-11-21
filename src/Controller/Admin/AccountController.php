<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class AccountController extends Controller
{
    public function updateAction(Base $app)
    {
        $user = $this->user->getUser();
        $this->validator->handle('account', function($app, $data) use ($user) {
            $user->copyfrom($data);
            $user->save();

            $this->flash->add('success', 'Data sudah disimpan');
            $app->reroute();
        }, null, $user);

        $this->renderAdmin('account/update.html');
    }

    public function logoutAction(Base $app)
    {
        $this->user->logout();
        $app->reroute('homepage');
    }
}
