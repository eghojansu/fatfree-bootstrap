<?php

namespace App\Controller\Admin;

use App\Core\Controller;
use Base;

class SettingController extends Controller
{
    public function settingAction(Base $app)
    {
        $this->validator->handle('setting', [$this, 'onSettingUpdate']);

        $this->renderAdmin('setting/setting.html');
    }

    public function maintenanceAction(Base $app)
    {
        $this->validator->handle('maintenance', [$this, 'onSettingUpdate']);
        $app['options'] = $this->validator->optionOnOff();

        $this->renderAdmin('setting/maintenance.html');
    }

    public function onSettingUpdate(Base $app, array $data)
    {
        $this->config->setValues($data);
        $this->flash->add('success', 'Pengaturan sudah disimpan');
        $app->reroute();
    }
}
