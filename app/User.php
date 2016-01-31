<?php

/**
 * Simple user class
 *
 * @author  Eko Kurniawan <fkurniawan@outlook.com>
 */
class User
{
    const SESSION_ID = 'faapps';
    private static $data = [];

    public static function isGuest()
    {
        return empty(Base::instance()->get('SESSION.'.self::SESSION_ID));
    }

    public static function wasLogged()
    {
        return !self::isGuest();
    }

    public static function is($type)
    {
        return self::data('type')===$type;
    }

    public static function clear()
    {
        Base::instance()->clear('SESSION.'.self::SESSION_ID);
    }

    public static function save($id, $type, $data)
    {
        self::$data = [
            'id'=>$id,
            'type'=>$type,
            'data'=>$data,
            ];
        Base::instance()->set('SESSION.'.self::SESSION_ID, self::$data);
    }

    public static function info($name)
    {
        $info = self::data('info');

        return isset($info[$name])?$info[$name]:null;
    }

    public static function data($name = null)
    {
        if (empty(self::$data)) {
            self::$data = Base::instance()->get('SESSION.'.self::SESSION_ID);
        }

        return $name?(isset(self::$data[$name])?self::$data[$name]:null):self::$data;
    }
}