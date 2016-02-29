<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacRoles extends AbstractMapper
{
    protected $rules = [
        'role_name'=>'unique'
    ];

    protected $labels = [
        'role_name' => 'Role',
        'role_description' => 'Description',
        'role_homepage' => 'Homepage',
    ];

    public function getFreeUserRole($user_id)
    {
        $sql = <<<SQL
SELECT role_id, role_name
FROM {$this->table}
WHERE role_id NOT IN (
    SELECT role_id
    FROM rbac_users_roles
    WHERE user_id = {$user_id}
)
ORDER BY role_id
SQL;

        return $this->db->exec($sql);
    }
}