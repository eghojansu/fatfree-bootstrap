<?php

require __DIR__.'/app/bootstrap.php';

$base = Base::instance();
$base->set('DEBUG', 3);
$base->run();
