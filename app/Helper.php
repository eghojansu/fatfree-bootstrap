<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

class Helper
{
    public static function greeting()
    {
        $hour = (int) date('H');
        if ($hour < 12)
            return 'good_morning';
        elseif ($hour < 16)
            return 'good_afternoon';
        elseif ($hour < 20)
            return 'good_evening';
        else
            return 'good_night';
    }

    public static function dump($var, $halt = false)
    {
        echo '<pre>'.var_dump($var, true).'</pre>';

        if ($halt) {
            die;
        }
    }
}