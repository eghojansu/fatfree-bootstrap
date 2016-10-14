<?php

namespace app\controller;

use app\BaseController;
use app\form\LoginForm;
use app\form\ProfileForm;

class DefaultController extends BaseController
{
    public function main($base)
    {
        $this->checkLogin();

        $this->render('default/default.html');
    }

    public function login($base)
    {
        $this->userOnly();

        $form = new LoginForm;

        if ($this->isSubmitted() && $form->validation->validate()) {
            if ($this->user->authenticate($form->get('username'), $form->get('password'))) {
                $this->flash('info', 'Welcome back '.$form->get('username'));

                $this->refresh();
            }

            $base->set('error', 'Login gagal! Cek kembali username dan password Anda.');
        }

        $base->set('form', $form);

        $this->render('default/login.html');
    }

    public function profile($base)
    {
        $this->checkLogin();

        echo 'You need define User Provider that implements DB\\Cursor to get this things work<br>';
        die(__FILE__.':'.__LINE__);

        $user = $this->user->getProvider()->reload();
        $profile = new ProfileForm($user);

        $except = ['id','password','submitted','old_password','new_password'];
        if ($this->isSubmitted() && $profile->assignFromRequest($except)->validation->validate()->valid()) {
            if ($newPassword = $profile->get('new_password')) {
                $this->user->updatePassword($newPassword);
            }
            $this->user->update();
            $this->flash('success', 'Data sudah diupdate');

            $this->refresh();
        }

        $base->set('profile', $profile);

        $this->render('main/profile.html');
    }

    public function logout()
    {
        $this->user->logout();
        $this->flash('warning', 'Anda sudah logout');
        $this->redirect('@homepage');
    }
}
