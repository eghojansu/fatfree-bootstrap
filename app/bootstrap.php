<?php

require __DIR__.'/../vendor/autoload.php';

$base = Base::instance();
$config = [
    'LOGS'=>'var/logs/',
    'TEMP'=>'var/tmp/',
    'UPLOADS'=>'var/uploads/',
    'CACHE'=>true,
    'TZ'=>'Asia/Jakarta',
    'UI'=>'app/view/',
];
$base->mset($config);

$base->config('app/config/app.ini');
$base->config('app/config/routes.ini');
$base->config('app/config/redirects.ini');
$base->config('app/config/maps.ini');

$db = $base->get('app.mysql');
$dsn = "mysql:host=$db[host];dbname=$db[name]";
$base->set('DB.SQL', new DB\SQL($dsn, $db['user'], $db['password']));

$template = Template::instance();
$template->filter('path', function($path) use ($base) {
    if (false === strpos($path, '/') && $p = $base->get('ALIASES.'.$path)) {
        $path = $p;
    }
    return $base->get('BASE').'/'.$path;
});

