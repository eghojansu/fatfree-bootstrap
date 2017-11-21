<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class LaporanController extends Controller
{
    public function userAction(Base $app)
    {
        $app['report'] = $this->report;
        $app['pagination'] = $this->entity->user->reportRekapUser();

        $this->renderAdmin('laporan/rekap_user.html');
    }

    public function userPrintAction(Base $app)
    {
        $app['report'] = $this->report;
        $app['items'] = $this->entity->user->reportRekapUserPrint();

        $this->renderPrint('admin/laporan/rekap_user_print.html');
    }
}
