<?php

namespace App\Service;

use Nutrition\Security\Authorization;
use Nutrition\Utils\Menu as BaseMenu;

class Menu extends BaseMenu
{
    /** @var boolean */
    private $isAdmin;

    /** @var boolean */
    private $isSuperAdmin;

    /** @var boolean */
    private $accessNotPrepared = true;


    public function getMenu($nav)
    {
        switch ($nav) {
            case 'home': return $this->getHome();
            case 'dashboard': return $this->getDashboard();
            case 'account': return $this->getDashboardAccount();
            case 'help': return $this->getHelp();
            return [];
        }
    }

    public function getHome()
    {
        return [
            ['route'=>'homepage', 'label'=>'Homepage'],
            ['route'=>'news', 'label'=>'News'],
            ['route'=>'about', 'label'=>'About'],
        ];
    }

    public function getDashboard()
    {
        $this->prepareAccess();

        $menu = [];
        $menu[] = ['route'=>'dashboard', 'label'=>'Dashboard'];
        if ($this->isAdmin) {
            $menu[] = [
                'label'=>'Laporan',
                'items'=>[
                    ['route'=>'laporan_user', 'label'=>'Rekapitulasi User'],
                ]
            ];
        }
        if ($this->isSuperAdmin) {
            $menu[] = [
                'label'=>'Pengaturan',
                'items'=>[
                    ['route'=>'setting', 'label'=>'Pengaturan Sistem'],
                    ['route'=>'setting_maintenance', 'label'=>'Maintenance'],
                    ['route'=>'user', 'label'=>'Data User'],
                    ['route'=>'db', 'label'=>'Database'],
                ]
            ];
        }
        $menu[] = [
            'label'=>'Bantuan',
            'items'=>[
                ['route'=>'help', 'label'=>'Daftar Isi'],
                ['route'=>'help_about', 'label'=>'About'],
            ]
        ];

        return $menu;
    }

    public function getDashboardAccount()
    {
        return [
            [
                'label'=>'Akun',
                'items'=>[
                    ['route'=>'admin_update', 'label'=>'Profile'],
                    ['route'=>'admin_logout', 'label'=>'Logout'],
                ],
            ],
        ];
    }

    public function getHelp()
    {
        $this->prepareAccess();

        $menu = [];
        $menu['Pengantar'] = 'index';
        $menu['Dashboard'] = 'dashboard';
        if ($this->isAdmin) {
            $menu['Laporan User'] = 'laporan_user';
        }
        if ($this->isSuperAdmin) {
            $menu['Pengaturan Sistem'] = 'manage_setting';
            $menu['Kelola User'] = 'manage_user';
        }
        $menu['Akun'] = 'account';

        return $menu;
    }

    protected function prepareAccess()
    {
        if ($this->accessNotPrepared) {
            $authorization = Authorization::instance();
            $this->isAdmin = $authorization->isGranted('ROLE_ADMIN');
            $this->isSuperAdmin = $authorization->isGranted('ROLE_SUPER_ADMIN');
            $this->accessNotPrepared = false;
        }
    }
}
