<?php

use app\model\UserMap;
use Nutrition\Url;
use Nutrition\Helper;

require __DIR__.'/../vendor/autoload.php';

$base = Base::instance();
$config = [
    'LOCALES'=>'app/dict/',
    'LOGS'=>'var/logs/',
    'TEMP'=>'var/tmp/',
    'UPLOADS'=>'var/uploads/',
    'CACHE'=>'folder=var/cache/',
    // 'CACHE'=>true,
    'UI'=>'app/view/',
    'LANGUAGE'=>'id',
    'TZ'=>'Asia/Jakarta',
    'SECURITY'=>[
        'provider'=>UserMap::class,
    ],
    'APPDIR'=>$base->fixslashes(__DIR__).'/',
    'ROOTDIR'=>$base->fixslashes(dirname(__DIR__)).'/',
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
$natives = [];
foreach ($natives as $native) {
    $template->filter($native, $native);
}
$template->filter('path', Url::class.'::instance()->path');
$template->filter('reverseDate', Helper::class.'::reverseDate');
$template->filter('title', function($title) use ($base) {
    $default = $base->get('app.name');

    return $title?$title.' ~ '.$default:$default;
});
