<?php

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class RbacUsers extends AbstractMapper
{
    protected $rules = [
        'username' => 'unique'
    ];
}