<?php

require __DIR__.'/vendor/autoload.php';

$config = [
    'DEBUG'=>3,
];

App::initBase($config)->run();