<?php

namespace App\Entity;

use Nutrition\SQL\Mapper;

class UserLog extends Mapper
{
    public function getOnlineVisitor()
    {
        return $this->count([
            'UserID is null and Active and (? - Stamp) < 300',
            time()
        ]);
    }

    public function getOnlineUser()
    {
        return $this->count([
            'UserID is not null and Active and (? - Stamp) < 300',
            time()
        ]);
    }

    public function getStatistic()
    {
        $sql = 'SELECT FROM_UNIXTIME(Stamp, "%Y-%m-%d") as gdate, count(SessionID) as gcount'.
                ' from '.self::tableName().
                ' group by FROM_UNIXTIME(Stamp, "%Y-%m-%d")';

        return $this->db->exec($sql);
    }
}
