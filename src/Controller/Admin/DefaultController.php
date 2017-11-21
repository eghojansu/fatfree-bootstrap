<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class DefaultController extends Controller
{
    public function dashboardAction(Base $app)
    {
        $app['healthy'] = $this->db->isHealthy();
        $app['last_login'] = $this->user->getUser()->getLastLogin();

        $this->renderAdmin('default/dashboard.html');
    }

    public function statisticAction(Base $app)
    {
        $this->json($this->entity->userLog->getStatistic());
    }

    public function userInfoAction(Base $app)
    {
        $userLog = $this->entity->userLog;
        $this->json([
            'visitor' => $userLog->getOnlineVisitor(),
            'user' => $userLog->getOnlineUser(),
        ]);
    }
}
