<?php

namespace App\Entity;

use Nutrition\SQL\Mapper;

class Setting extends Mapper
{
    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('created_at')) {
            $that->set('created_at', self::sqlTimestamp());
        }
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('updated_at', self::sqlTimestamp());
    }
}
