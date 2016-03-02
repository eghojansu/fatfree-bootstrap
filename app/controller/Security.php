<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace controller;

use Controller;
use Nutrition;

class Security extends Controller
{
    public function login()
    {
        $this->guestOnly();
        $this->render('login.htm', true);
    }

    public function processLogin($app)
    {
        $this->guestOnly();

        $username = $app->get('POST.username');
        $password = $app->get('POST.password');

        if ($this->user->authenticate($username, $password)) {
            $this->flash('welcome', 1);
            $this->redirectTo('@dashboard');
        }

        $this->flash('username', $app->get('POST.username'));
        $this->flash('error', 'login_failed');
        $this->goBack();
    }

    public function logout()
    {
        $this->user->logout();
        $this->goHome();
    }
}