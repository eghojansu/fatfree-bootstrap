<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacUsersRoles extends AbstractMapper
{
    protected $rules = [
        'role_id' => 'lookup(model\\RbacRoles)',
        'user_id' => 'lookup(model\\RbacUsers)',
    ];
}