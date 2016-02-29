<?php

use Nutrition\Security\UserProviderInterface;
use Nutrition\Security\PasswordEncoderInterface;
use model\Profiles;
use model\RbacUsers;
use model\ViewRbacUsersPermissions;
use model\ViewRbacUsersRoles;

/**
 * User provider
 */
class UserProvider implements UserProviderInterface
{
    /**
     * Map to rbac user table
     * @var model\RbacUsers
     */
    public $data;
    /**
     * Map to rbac user table
     * @var model\Profiles
     */
    public $profile;
    /**
     * Permissions
     * @var array
     */
    protected $permissions = [];
    /**
     * Roles
     * @var array
     */
    protected $roles = [];

    /**
     * Min user password
     */
    const PASSWORD_MIN = 5;
    /**
     * Max user password
     */
    const PASSWORD_MAX = 20;

    public function __construct()
    {
        $this->data = (new RbacUsers)->setTTL(0);
        $this->profile = (new Profiles)->setTTL(0);
    }

    /**
     * Authenticate user
     * @param  string $username
     * @return bool
     */
    public function authenticate($username)
    {
        $this->data->load(['username = ?', $username], ['limit'=>1]);

        return $this->data->valid();
    }

    /**
     * Load user data
     * @param  mixed $id user id
     */
    public function loadUserData($id)
    {
        $this->data->findByPK($id);
        $this->profile->load(['user_id = ?', $id], ['limit'=>1]);
    }

    /**
     * Get session id
     * @return string
     */
    public function getSessionID()
    {
        return 'faapps';
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword()
    {
        return $this->data->password;
    }

    /**
     * Get user id
     * @return mixed
     */
    public function getID()
    {
        return $this->data->user_id;
    }

    /**
     * Check permission and compare with $compare
     * @param  string  $perm
     * @param  boolean $compare
     * @return boolean
     */
    public function hasPermission($perm, $compare = true)
    {
        $perm = strtolower($perm);
        if (!isset($this->permissions[$perm])) {
            $map = new ViewRbacUsersPermissions;
            $map->load(['user_id = ? and permission_name = ?', $this->getID(), $perm], ['limit'=>1]);

            return ($this->permissions[$perm] = $map->valid()) === $compare;
        }

        return $this->permissions[$perm] === $compare;
    }


    /**
     * Check role and compare with $compare
     * @param  string  $role
     * @param  boolean $compare
     * @return boolean
     */
    public function hasRole($role, $compare = true)
    {
        $role = strtolower($role);
        if (!isset($this->roles[$role])) {
            $map = new ViewRbacUsersRoles;
            $map->load(['user_id = ? and role_name = ?', $this->getID(), $role], ['limit'=>1]);

            return ($this->roles[$role] = $map->valid()) === $compare;
        }

        return $this->roles[$role] === $compare;
    }

    /**
     * Get all permission, can be used to init user permissions
     * @return array
     */
    public function getPermissions()
    {
        $filter = ['user_id = ?', $this->getID()];
        if ($checked_permissions = array_keys($this->permissions)) {
            $filter[0] .= ' and permission_name not in ('.str_repeat('?, ', count($checked_permissions)-1).'?)';
            $filter = array_merge($filter, $checked_permissions);
        }
        $permissions = (new ViewRbacUsersPermissions)->select('permission_name', $filter);
        foreach ($permissions as $permission) {
            $this->permissions[strtolower($permission->permission_name)] = true;
        }

        return $this->permissions;
    }

    /**
     * Get all role, can be used to init user roles
     * @return array
     */
    public function getRoles()
    {
        $filter = ['user_id = ?', $this->getID()];
        if ($checked_roles = array_keys($this->roles)) {
            $filter[0] .= ' and role_name not in ('.str_repeat('?, ', count($checked_roles)-1).'?)';
            $filter = array_merge($filter, $checked_roles);
        }
        $roles = (new ViewRbacUsersRoles)->select('role_name', $filter);
        foreach ($roles as $role) {
            $this->roles[strtolower($role->role_name)] = true;
        }

        return $this->roles;
    }

    /**
     * Update user data
     * @param  string|array             $source
     * @param  string                   $username
     * @param  string                   $password
     * @param  string                   $oldPassword
     * @param  PasswordEncoderInterface $encoder
     * @return string                   error message when update fail, empty string when success
     */
    public function update($source, $username, $password, $oldPassword, PasswordEncoderInterface $encoder)
    {
        $result = 'update_succed';
        $id = $this->profile->profile_id;
        $this->profile->copyfrom($source);
        $this->profile->profile_id = $id;
        $this->profile->user_id = $this->data->user_id;
        $this->data->username = $username;
        $this->data->password = $oldPassword;
        $passwordOK = true;
        if ($password) {
            $passwordLength = strlen($password);
            $minPassed = (!self::PASSWORD_MIN || $passwordLength >= self::PASSWORD_MIN);
            $maxPassed = (!self::PASSWORD_MAX || $passwordLength <= self::PASSWORD_MAX);
            $passwordOK = $minPassed && $maxPassed;
        }
        if ($passwordOK && $this->profile->validate() && $this->data->validate()) {
            $this->data->password = $encoder->encode($password?:$this->data->password);
            $this->data->save();
            $this->profile->save();
        } else {
            $errorData = $this->data->getAllErrorString();
            $errorProfile = $this->profile->getAllErrorString();
            $errorLength = 'Password minimum '.self::PASSWORD_MIN.' char, maximum '.self::PASSWORD_MAX.' char';

            $result = $errorProfile?:($errorData?:$errorLength);
        }

        return $result;
    }
}