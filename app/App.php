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
     * Construct DB\SQL
     * @return DB\SQL
     */
    public static function database()
    {
        if (!Registry::exists('database')) {
            $db = Base::instance()->get('database');

            return Registry::set('database', new DB\SQL(
                'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'],
                $db['user'],
                $db['password']
            ));
        }

        return Registry::get('database');
    }

    /**
     * Construct SQL Mapper
     * @param string $table
     * @param DB\SQL $db
     * @return DB\SQL\Mapper
     */
    public static function map($table, DB\SQL $db = null)
    {
        return new DB\SQL\Mapper($db?:self::database(), $table);
    }

    /**
     * Generate new ID based on format
     * @param DB\Cursor $map
     * @param string $columName
     * @param string $format
     * @return string
     */
    public static function newID($map, $columnName, $format)
    {
        $clone = clone $map;
        $clone->load(null, [
            'limit'=>1,
            'order'=>$columnName.' desc',
            ]);

        $last = 0;
        $boundPattern = '/\{([a-z0-9\- _\.]+)\}/i';
        if ($clone->valid()) {
            $pattern = preg_replace_callback($boundPattern, function($match) {
                return is_numeric($match[1])?
                    '(?<serial>'.str_replace('9', '[0-9]', $match[1]).')':
                    '(?<date>.{'.strlen(date($match[1])).'})';
            }, $format);
            if (preg_match('/^'.$pattern.'$/i', $clone[$columnName], $match))
                $last = $match['serial']*1;
        }

        return preg_replace_callback($boundPattern, function($match) use ($last) {
            return is_numeric($match[1])?
                str_pad($last+1, strlen($match[1]), '0', STR_PAD_LEFT):
                date($match[1]);
        }, $format);
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

    /**
     * Render template view
     * @param  string $view
     * @param  string $template
     * @param  string $key
     * @return null
     */
    public static function render($view, $template = null, $key = 'content')
    {
        $app = Base::instance();
        $template = $template?:$app->get('app.template');
        if ($template) {
            $app->set($key, $view);
            echo Template::instance()->render($template);
        } else
            echo Template::instance()->render($view);
    }
}