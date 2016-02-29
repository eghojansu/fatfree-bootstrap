<?php

require __DIR__.'/vendor/autoload.php';

$config = [
    'DEBUG' => 3,
    'ONERROR' => function($app) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo Template::instance()->render('error.htm');
    },
];

Nutrition::bootstrap($config)->run();