<?php

/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

abstract class DashboardController extends Controller
{
    /**
     * Breadcrumb
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * Override parent beforeroute
     */
    public function beforeroute($app, $params)
    {
        parent::beforeroute($app, $params);
        $this->userOnly();
        $permissions = $this->user->provider->getPermissions();
        $menu = [
            'dashboard' => ['icon'=>'graph','label'=>$app->get('dashboard')],
            'rbac'=>['icon'=>'users','label'=>$app->get('user_management'),'hide'=>$this->user->provider->hasPermission('read user', false)],
        ];
        $this->breadcrumb = new Breadcrumb;
        $app->set('menu.items', $menu);
        $app->set('breadcrumb', $this->breadcrumb);
    }
}