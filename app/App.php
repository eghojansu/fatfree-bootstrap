<?php

/**
 * App helper
 *
 * @author Eko Kurniawan <fkurniawan@outlook.com>
 */
final class App
{
    /**
     * Init fatfree Base class and register some filter rule
     * @return Base instance
     */
    public static function initBase(array $opt = [], $config = 'app/config/configs.ini')
    {
        $app = Base::instance();
        $app->mset($opt);
        $app->config($config);

        Template::instance()->filter('url', 'App::url');
        Template::instance()->filter('asset', 'App::asset');

        return $app;
    }

    /**
     * Generate base url
     * @return string
     */
    public static function baseUrl()
    {
        $app = Base::instance();

        static $base;

        if (empty($base)) {
            $base = $app['SCHEME'].'://'.$_SERVER['SERVER_NAME'].
                ($app['PORT'] && $app['PORT']!=80 && $app['PORT']!=443?
                        (':'.$app['PORT']):'').$app['BASE'];
        }

        return $base;
    }

    /**
     * Generate url based on alias
     * @param string $alias
     * @param mixed $param
     * @return string
     */
    public static function url($alias, $params = null)
    {
        return self::baseUrl().Base::instance()->alias($alias, $params);
    }

    /**
     * Generate assets url
     * @param string $path
     * @return string
     */
    public static function asset($path)
    {
        return self::baseUrl().'/'.$path;
    }

    /**
     * Get or set flash session
     * @param string $var
     * @param mixed $val
     * @return $val
     */
    public static function flash($var, $val = null)
    {
        $app = Base::instance();
        $var = 'SESSION.'.$var;

        if (is_null($val)) {
            $val = $app->get($var);
            $app->clear($var);

            return $val;
        }

        return $app->set($var, $val);
    }

    /**
     * Send json
     * @param array $output
     */
    public static function jsonOut(array $output)
    {
        header('Content-type: application/json');

        echo json_encode($output);
        die;
    }

    /**
     * Prepend each array key with prefix
     * @param array $array
     * @param string $prefix
     * @return array
     */
    public static function prependKey(array $array, $prefix = ':')
    {
        return array_combine(array_map(function($a) use ($prefix) {
            return $prefix.$a;
        }, array_keys($array)), array_values($array));
    }

    /**
     * TitleIze string
     * @param  string $str
     * @return string
     */
    public static function titleIze($str)
    {
        return ucwords(implode(' ', array_filter(explode('_', Base::instance()->snakecase($str)))));
    }

    /**
     * Get class name from namespace
     * @param  string $ns
     * @return string
     */
    public static function className($ns)
    {
        $class = strrchr($ns, '\\');
        $class || $class = $ns;

        return ltrim($class, '\\');
    }

    /**
     * Classname to table
     * @param  string $ns namespaec
     * @return string
     */
    public static function classNameToTable($ns)
    {
        return Base::instance()->snakeCase(lcfirst(self::className($ns)));
    }

    /**
     * Get directory content
     * @param  string $dirname
     * @param  boolean $recursive
     * @return array
     */
    public static function dirContent($dirname, $recursive = false)
    {
        $dir = new DirectoryIterator($dirname);
        $content = [];
        foreach ($dir as $file)
            if (!$file->isDot()) {
                if ($file->isDir() && $recursive)
                    $content = array_merge($content, self::dirContent($file->getPathname(), $recursive));
                else
                    $content[] = $file->getPathname();
            }

        return $content;
    }
}