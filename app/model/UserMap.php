<?php

namespace app\model;

use Bcrypt;
use Nutrition\SQLMapper;
use Nutrition\UserProviderInterface;

// class User extends SQLMapper implements UserProviderInterface
class UserMap implements UserProviderInterface
{
    protected $source = 'user';
    protected $labels = [
        'old_password'=>'Password',
        'new_password'=>'Password Baru',
    ];

    /**
     * @see source
     */
    public function getRoles()
    {
        return ['ADMIN'];
        // return explode(',', $this->roles);
    }

    /**
     * @see source
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @see source
     */
    public function isBlocked()
    {
        return false;
    }

    /**
     * @see source
     */
    public function validatePassword($plainPassword)
    {
        return Bcrypt::instance()->verify($plainPassword, Bcrypt::instance()->hash('admin'));
        // return Bcrypt::instance()->verify($plainPassword, $this->password);
    }

    /**
     * @see source
     */
    public function encryptPassword($plainPassword)
    {
        return Bcrypt::instance()->hash($plainPassword);
    }

    /**
     * @see source
     */
    public function loadUser($value)
    {
        return $this;
        // return $this->load(['username = ?', $value])?:$this;
    }

    /**
     * @see source
     */
    public function reload()
    {
        return $this;
        // return $this->load(['id = ?', $this->id])?:$this;
    }

    /**
     * @see source
     */
    public function updatePassword($plainPassword)
    {
        // $this->set('password', $this->encryptPassword($plainPassword));

        return $this;
    }

    /**
     * @see source
     */
    public function valid()
    {
        return true;
        // return parent::valid();
    }

    /**
     * @see source
     */
    public function cast()
    {
        return ['nama'=>'Admin'];
        // return parent::cast();
    }

    /**
     * @see source
     */
    public function copyfrom($var,$func=NULL)
    {
        // parent::copyfrom($var, $func);

        return $this;
    }

    /**
     * @see source
     */
    public function update()
    {
        return true;
        // return parent::update();
    }
}
