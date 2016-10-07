<?php

namespace app\controller;

use app\BaseController;
use app\form\Login;

class Main extends BaseController
{
    public function index($base)
    {
        $form = new Login;
        $base->set('form', $form);

        $this->noTemplate()->render('main.html');
    }
}
