<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class ViewRbacUsersRoles extends AbstractMapper
{
    public function getUserRole($user_id)
    {
        return $this
            ->setTTL(0)
            ->addFilter(['user_id', $user_id])
            ->orderBy('user_id, role_id')
            ->select('role_id, role_name', null);
    }
}