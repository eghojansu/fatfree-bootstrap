<?php

namespace App\Utils;

use Bumbon\Validation\ViolationList;
use Nutrition\Utils\CommonUtil;

class Utils
{
    protected static $violation;

    public static function welcomeTime($now = null)
    {
        return CommonUtil::lowerLabel($now ?: date('H'), [
            10 => 'Selamat pagi',
            14 => 'Selamat siang',
            17 => 'Selamat sore',
            25 => 'Selamat malam'
        ]);
    }

    public static function violationSet(ViolationList $violation = null)
    {
        self::$violation = $violation;
    }

    public static function violationHasError($key)
    {
        return self::$violation && self::$violation->exists($key) ? 'has-error' : '';
    }

    public static function violationWriteError($key, $sep = '<br>')
    {
        return self::$violation && self::$violation->exists($key) ?
            '<span class="help-block">'.implode($sep, self::$violation->get($key)).'</span>' : '';
    }
}
