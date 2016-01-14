<?php

namespace controllers;

class Main extends \AbstractController
{
    public function home()
    {
        $this->setContent('main/home.htm');
    }
}