<?php

namespace controllers;

use App;
use Controller;

class Main extends Controller
{
    public function home()
    {
        $this->render('main/home.htm');
    }
}