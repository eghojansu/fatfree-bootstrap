<?php

require __DIR__ . '/../vendor/autoload.php';

$app = Base::instance();
$kernel = new App\Kernel('dev');
$kernel
    ->loadConfiguration($app)
    ->registerTemplateFilters()
    ->addNutrition()
    ->registerSession()
;
$app->run();
