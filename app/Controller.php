<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

use Nutrition\AbstractController;

/**
 * Basic controller
 */
abstract class Controller extends AbstractController
{
    /**
     * Permission checker, return true if current user has permission
     * or redirect to $target or show error forbidden
     * @param  string|array $perms
     * @param  string $target url/route
     * @return bool
     */
    protected function grantWhenHasPermission($perms, $target = null)
    {
        foreach (is_array($perms)?$perms:[$perms] as $perm) {
            if ($this->user->provider->hasPermission($perm, false)) {
                $target?$this->redirectTo($target):$this->errorForbidden('You have no permission to access this page');
            }
        }

        return true;
    }

    /**
     * Permission checker, return true if current user has role
     * or redirect to $target or show error forbidden
     * @param  string|array $roles
     * @param  string $target url/route
     * @return bool
     */
    protected function grantWhenHasRole($roles, $target = null)
    {
        foreach (is_array($roles)?$roles:[$roles] as $role) {
            if ($this->user->provider->hasRole($role, false)) {
                $target?$this->redirectTo($target):$this->errorForbidden('You have no permission to access this page');
            }
        }

        return true;
    }
}