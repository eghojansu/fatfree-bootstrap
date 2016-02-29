<?php

namespace controller;

use DashboardController;
use Nutrition;
use model\RbacRoles as RolesRepository;
use model\RbacPermissions;
use model\RbacRolesPermissions;
use model\ViewRbacRolesPermissions;

class RbacRoles extends DashboardController
{
    public function home($app)
    {
        $this->grantWhenHasPermission('read role');
        $model = new RolesRepository;
        $app->set('page', $model
            ->setTTL(0)
            ->orderBy('role_id, role_name')
            ->addFilter(['role_name', $app->get('GET.keyword'), 'contain'])
            ->paginate($this->getPageNumber(), $this->getPageLimit()));
        $app->set('model', $model);
        $this->render('rbac/roles/home.htm');
    }

    public function input($app, $params)
    {
        $this->grantWhenHasPermission(['create role', 'update role']);
        $model = $this->model($params['role']);
        $model->copyfrom($this->flash('posted')?:[]);
        $app->set('model', $model);
        $this->breadcrumb->append('input');
        $this->render('rbac/roles/form.htm');
    }

    public function inputProcess($app, $params)
    {
        $this->grantWhenHasPermission(['create role', 'update role']);
        $model = $this->model($params['role']);
        $model->copyfrom('POST');
        if ($model->safeSave()) {
            $this->redirectTo('@rbacRole');
        }
        $this->flash('posted', $app->get('POST'));
        $this->flash('error', $model->getAllErrorString());
        $this->redirectTo('@rbacRoleInput');
    }

    public function delete($app, $params)
    {
        $this->grantWhenHasPermission('delete role');
        $this->model($params['role'], true)->erase();
        $this->redirectTo('@rbacRole');
    }

    public function detail($app, $params)
    {
        $this->grantWhenHasPermission('read role');
        $permission = new RbacPermissions;
        $app->set('model', $this->model($params['role'], true));
        $app->set('model2', $permission);
        $app->set('freePermissions', $permission->getFreeRolePermission($params['role']));
        $app->set('permissions', (new ViewRbacRolesPermissions)->getRolePermission($params['role']));
        $this->breadcrumb->append('detail');
        $this->render('rbac/roles/detail.htm');
    }

    public function addPermission($app, $params)
    {
        $this->grantWhenHasPermission('create role permission');
        $permissions = $app->get('POST.permissions')?:[];
        $model = new RbacRolesPermissions;
        foreach ($permissions as $key => $value) {
            $data = [
                'permission_id'=>$value,
                'role_id'=>$params['role'],
                ];
            $model->findByPK($data);
            if ($model->valid()) {
                continue;
            }
            $model->copyfrom($data);
            if (!$model->safeSave()) {
                $this->flash('error', $model->getAllErrorString());
                break;
            }
        }
        $this->goBack();
    }

    public function deletePermission($app, $params)
    {
        $this->grantWhenHasPermission('delete role permission');
        $model = new RbacRolesPermissions;
        $model->findByPK([
            'permission_id'=>$params['permission'],
            'role_id'=>$params['role'],
            ]);
        $model->erase();
        $this->goBack();
    }

    protected function model($id, $required = false)
    {
        $model = new RolesRepository;
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
        $this->breadcrumb->setRoot('User Management', Nutrition::url('rbac'), 'users');
        $this->breadcrumb->append('Roles', Nutrition::url('rbacRole'));
        $app->set('menu.active', 'rbac');
        $app->set('can', [
            'insert'=>$this->user->provider->hasPermission('create role'),
            'update'=>$this->user->provider->hasPermission('update role'),
            'delete'=>$this->user->provider->hasPermission('delete role'),
            'read'=>$this->user->provider->hasPermission('read role'),
            'permission'=>[
                'insert'=>$this->user->provider->hasPermission('create role permission'),
                'update'=>$this->user->provider->hasPermission('update role permission'),
                'delete'=>$this->user->provider->hasPermission('delete role permission'),
                'read'=>$this->user->provider->hasPermission('read role permission'),
            ],
        ]);
    }
}