<?php

namespace App\Controller;

use App\Core\Controller;
use Base;

class MaintenanceController extends Controller
{
    public function beforeroute(Base $app, array $params)
    {
        parent::beforeroute($app, $params);

        if (
            !$this->config->isMaintenance()
            && !$this->access->isGranted('ROLE_SUPER_ADMIN')
        ) {
            $app->error(404);
        }
    }

    public function maintenanceAction(Base $app)
    {
        $this->render('maintenance.html');
    }
}
