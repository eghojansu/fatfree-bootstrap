<?php

namespace App\Entity;

use Nutrition\SQL\Mapper;

class Configuration extends Mapper
{
    public function onMapBeforeInsert($that, array $pkeys)
    {
        if (!$that->get('CreatedAt')) {
            $that->set('CreatedAt', self::sqlTimestamp());
        }
    }

    public function onMapBeforeUpdate($that, array $pkeys)
    {
        $that->set('UpdatedAt', self::sqlTimestamp());
    }
}
