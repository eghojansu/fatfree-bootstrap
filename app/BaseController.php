<?php

namespace app;

use Base;
use Nutrition\Controller;

class BaseController extends Controller
{
    protected $template = 'layout/base.html';
    protected $homepage = '@homepage';
    protected $loginpage = '@login';

    protected function getPage($key = 'page')
    {
        $page = Base::instance()->get('GET.'.$key)*1;
        $page = $page > 1 ? $page - 1 : 0;

        return $page;
    }
}
