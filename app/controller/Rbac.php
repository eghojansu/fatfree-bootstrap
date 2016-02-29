<?php

namespace controller;

use DashboardController;
use Nutrition;

class Rbac extends DashboardController
{
    public function home($app)
    {
        $this->grantWhenHasPermission('read user');
        $links = [];
        if ($this->user->provider->hasPermission('read user')) {
            $links['rbacUser'] = $app->get('manage_users');
        }
        if ($this->user->provider->hasPermission('read permission')) {
            $links['rbacRole'] = $app->get('manage_roles');
        }
        if ($this->user->provider->hasPermission('read role')) {
            $links['rbacPermission'] = $app->get('manage_permissions');
        }
        $app->set('links', $links);
        $this->render('rbac/home.htm');
    }

    public function beforeroute($app, $params)
    {
        parent::beforeroute($app, $params);
        $this->breadcrumb->setRoot($app->get('user_management'), Nutrition::url('rbac'), 'users');
    }
}