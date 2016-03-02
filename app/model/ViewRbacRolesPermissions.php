<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class ViewRbacRolesPermissions extends AbstractMapper
{
    public function getRolePermission($role_id)
    {
        return $this
            ->setTTL(0)
            ->addFilter('role_id', $role_id)
            ->orderBy('role_id, permission_id')
            ->select('permission_id, permission_name', null);
    }
}