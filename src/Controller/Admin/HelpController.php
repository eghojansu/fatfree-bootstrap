<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class HelpController extends Controller
{
    public function tocAction(Base $app)
    {
        $this->renderAdmin('help/toc.html');
    }

    public function aboutAction(Base $app)
    {
        $this->renderAdmin('help/about.html');
    }
}
