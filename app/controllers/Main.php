<?php

namespace controllers;

use App;

class Main
{
    public function home()
    {
        App::render('main/home.htm');
    }
}