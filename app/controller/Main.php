<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

namespace controller;

use Controller;

class Main extends Controller
{
    public function home()
    {
        $this->render('landing.htm', true);
    }

    public function language($app, $params)
    {
        $app->set('SESSION.lang', $params['lang']);
        $this->goBack();
    }
}