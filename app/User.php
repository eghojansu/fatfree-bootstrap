<?php

use Nutrition\Security\UserProviderInterface;

class User implements UserProviderInterface
{
    public function authenticate($username)
    {
        return true;
    }

    public function loadUserData($id)
    {
        // loading
    }

    public function getSessionID()
    {
        return 'faapps';
    }

    public function getPassword()
    {
        return 'password';
    }

    public function getID()
    {
        return 1;
    }
}