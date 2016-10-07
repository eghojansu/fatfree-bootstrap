<?php

namespace app\form;

use Nutrition\Form;

class Login extends Form
{
    protected $attrs = [
        'class'=>'form-horizontal',
    ];
    protected $controlAttrs = [
        'class'=>'form-control',
    ];
    protected $labelAttrs = [
        'class'=>'form-label col-sm-2',
    ];
}
