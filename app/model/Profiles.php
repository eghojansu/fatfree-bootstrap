<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace model;

use Nutrition\DB\SQL\AbstractMapper;

class Profiles extends AbstractMapper
{
    protected $rules = [
        'user_id' => 'lookup(model\\RbacUsers)'
    ];
}