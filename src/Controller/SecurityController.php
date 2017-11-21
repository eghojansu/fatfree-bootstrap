<?php

namespace App\Controller;

use App\Core\Controller;
use Base;

class SecurityController extends Controller
{
    public function loginAction(Base $app)
    {
        $this->flash->add('warning', $this->auth->handleAttempt(
            $this->isPost,
            $app['POST.username'],
            $app['POST.password'],
            'dashboard'
        ));

        $this->renderAuth('security/login.html');
    }
}
