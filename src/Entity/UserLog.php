<?php

namespace App\Entity;

use Nutrition\SQL\Mapper;

class UserLog extends Mapper
{
    public function getOnlineVisitor()
    {
        return $this->count([
            'user_id is null and active and (? - stamp) < 300',
            time()
        ]);
    }

    public function getOnlineUser()
    {
        return $this->count([
            'user_id is not null and active and (? - stamp) < 300',
            time()
        ]);
    }

    public function getStatistic()
    {
        $sql = 'SELECT FROM_UNIXTIME(stamp, "%Y-%m-%d") as gdate, count(session_id) as gcount'.
                ' from '.self::tableName().
                ' group by FROM_UNIXTIME(stamp, "%Y-%m-%d")';

        return $this->db->exec($sql);
    }
}
