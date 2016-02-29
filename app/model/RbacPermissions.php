<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacPermissions extends AbstractMapper
{
    protected $rules = [
        'permission_name' => 'unique'
    ];

    protected $labels = [
        'permission_name' => 'Permission',
        'permission_description' => 'Description',
        'permission_homepage' => 'Homepage',
    ];

    public function getFreeRolePermission($role_id)
    {
        $sql = <<<SQL
SELECT permission_id, permission_name
FROM {$this->table}
WHERE permission_id NOT IN (
    SELECT permission_id
    FROM rbac_roles_permissions
    WHERE role_id = {$role_id}
)
ORDER BY permission_id
SQL;

        return $this->db->exec($sql);
    }
}