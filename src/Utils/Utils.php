<?php

namespace App\Utils;

use Nutrition\Utils\CommonUtil;

class Utils
{
    public static function welcomeTime($now = null)
    {
        return CommonUtil::lowerLabel($now ?: date('H'), [
            10 => 'Selamat pagi',
            14 => 'Selamat siang',
            17 => 'Selamat sore',
            25 => 'Selamat malam'
        ]);
    }
}
