<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace controller;

use DashboardController;
use Nutrition;

class Dashboard extends DashboardController
{
    public function home()
    {
        $this->render('dashboard/home.htm');
    }

    public function userProfile($app)
    {
        $this->breadcrumb->append($app->get('profile'));
        $this->render('dashboard/user-profile.htm');
    }

    public function updateProfile($app)
    {
        $source = 'POST';
        $username = $app->get('POST.username');
        $password = $app->get('POST.new_password');
        $oldPassword = $app->get('POST.password');

        if ($this->user->verify($oldPassword)) {
            $message = $this->user->provider->update($source, $username, $password, $oldPassword, $this->user->encoder);
        } else {
            $message = 'invalid_password';
        }

        $this->flash('error', $message);
        $this->goBack();
    }

    public function beforeroute($app, $params)
    {
        parent::beforeroute($app, $params);
        $this->breadcrumb->setRoot($app->get('dashboard'), Nutrition::url('dashboard'), 'dashboard');
    }
}