<?php

namespace controller;

use DashboardController;
use Nutrition;
use model\RbacPermissions as PermissionsRepository;

class RbacPermissions extends DashboardController
{
    public function home($app)
    {
        $this->grantWhenHasPermission('read permission');
        $model = new PermissionsRepository;
        $app->set('page', $model
            ->setTTL(0)
            ->addFilter(['permission_name', $app->get('GET.keyword'), 'contain'])
            ->orderBy('permission_id, permission_name')
            ->paginate($this->getPageNumber(), $this->getPageLimit()));
        $app->set('model', $model);
        $this->render('rbac/permissions/home.htm');
    }

    public function input($app, $params)
    {
        $this->grantWhenHasPermission(['create permission', 'update permission']);
        $model = $this->model($params['permission']);
        $model->copyfrom($this->flash('posted')?:[]);
        $app->set('model', $model);
        $this->breadcrumb->append('input');
        $this->render('rbac/permissions/form.htm');
    }

    public function inputProcess($app, $params)
    {
        $this->grantWhenHasPermission(['create permission', 'update permission']);
        $model = $this->model($params['permission']);
        $model->copyfrom('POST');
        if ($model->safeSave()) {
            $this->redirectTo('@rbacPermission');
        }
        $this->flash('posted', $app->get('POST'));
        $this->flash('error', $model->getAllErrorString());
        $this->redirectTo('@rbacPermissionInput');
    }

    public function delete($app, $params)
    {
        $this->grantWhenHasPermission('delete permission');
        $this->model($params['permission'], true)->erase();
        $this->redirectTo('@rbacPermission');
    }

    protected function model($id, $required = false)
    {
        $model = new PermissionsRepository;
        $model->findByPK($id);
        $model->setTTL(0);
        if ($model->dry() && $required) {
            $this->errorNotFound();
        }

        return $model;
    }

    public function beforeroute($app, $params)
    {
        parent::beforeroute($app, $params);
        $this->breadcrumb->setRoot($app->get('user_management'), Nutrition::url('rbac'), 'users');
        $this->breadcrumb->append($app->get('permissions'), Nutrition::url('rbacPermission'));
        $app->set('menu.active', 'rbac');
        $app->set('can', [
            'insert'=>$this->user->provider->hasPermission('create permission'),
            'update'=>$this->user->provider->hasPermission('update permission'),
            'delete'=>$this->user->provider->hasPermission('delete permission'),
            'read'=>$this->user->provider->hasPermission('read permission'),
        ]);
    }
}