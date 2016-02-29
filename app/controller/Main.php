<?php

namespace controller;

use Controller;

class Main extends Controller
{
    public function home()
    {
        $this->render('landing.htm', true);
    }
}