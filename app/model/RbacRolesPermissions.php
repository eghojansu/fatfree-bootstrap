<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacRolesPermissions extends AbstractMapper
{
    protected $rules = [
        'role_id' => 'lookup(model\\RbacRoles)',
        'permission_id' => 'lookup(model\\RbacPermissions)',
    ];
}