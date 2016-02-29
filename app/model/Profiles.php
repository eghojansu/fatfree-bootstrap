<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class Profiles extends AbstractMapper
{
    protected $rules = [
        'user_id' => 'lookup(model\\RbacUsers)'
    ];
}