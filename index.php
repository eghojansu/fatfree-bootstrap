<?php

require __DIR__.'/vendor/autoload.php';

$config = [
    'DEBUG'=>3,
];

Nutrition::bootstrap($config)->run();