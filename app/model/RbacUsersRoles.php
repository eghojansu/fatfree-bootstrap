<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacUsersRoles extends AbstractMapper
{
    protected $rules = [
        'role_id' => 'lookup(model\\RbacRoles)',
        'user_id' => 'lookup(model\\RbacUsers)',
    ];
}