<?php

namespace controller;

use DashboardController;
use Nutrition;
use Nutrition\DB\SQL\Connection;
use model\RbacUsers as UsersRepository;
use model\RbacUsersRoles;
use model\RbacRoles;
use model\Profiles;
use model\ViewProfilesUsers;
use model\ViewRbacUsersRoles;

class RbacUsers extends DashboardController
{
    public function home($app)
    {
        $this->grantWhenHasPermission('read user');
        $model = new ViewProfilesUsers;
        $app->set('page', $model
            ->setTTL(0)
            ->orderBy('user_id, username')
            ->addFilter(['username', $app->get('GET.keyword'), 'contain'])
            ->addFilter(['user_id', $this->user->provider->getID(), '<>'])
            ->paginate($this->getPageNumber(), $this->getPageLimit()));
        $app->set('model', $model);
        $this->render('rbac/users/home.htm');
    }

    public function input($app, $params)
    {
        $this->grantWhenHasPermission(['create user', 'update user']);
        $model = $this->modelA($params['user']);
        $model2 = $this->modelB($params['user']);
        $posted = $this->flash('posted')?:[];
        $model->copyfrom($posted);
        $model2->copyfrom($posted);
        $app->set('model', $model);
        $app->set('model2', $model2);
        $this->breadcrumb->append('input');
        $this->render('rbac/users/form.htm');
    }

    public function inputProcess($app, $params)
    {
        $this->grantWhenHasPermission(['create user', 'update user']);
        $model = $this->modelA($params['user']);
        $model2 = $this->modelB($params['user']);
        $db = Connection::getConnection();

        $old_password = $model->password;

        $model->copyfrom('POST');
        $model2->copyfrom('POST');
        $model2->active = (int) $app->exists('POST.active');

        $passwordOK = false;
        if ($model->password) {
            $model->password = $this->user->encoder->encode($model->password);
            $passwordOK = true;
        } else {
            if ($model->valid()) {
                $model->password = $old_password;
                $passwordOK = true;
            }
        }

        if ($passwordOK) {
            $db->begin();
            if ($model->safeSave()) {
                $model2->set('user_id', $model->user_id);
                if ($model2->safeSave()) {
                    $db->commit();
                    $this->redirectTo('@rbacUser');
                }
            }
            $db->rollback();
        } else {
            $model->addError('password', $app->get('invalid_password'));
        }

        $this->flash('posted', $app->get('POST'));
        $this->flash('error', $model->getAllErrorString().'<br>'.$model2->getAllErrorString());
        $this->redirectTo('@rbacUserInput');
    }

    public function delete($app, $params)
    {
        $this->grantWhenHasPermission('delete user');
        $this->modelA($params['user'], true)->erase();
        $this->modelB($params['user'], true)->erase();
        $this->redirectTo('@rbacUser');
    }

    public function detail($app, $params)
    {
        $this->grantWhenHasPermission('read user');
        $model = new ViewProfilesUsers;
        $roles = new RbacRoles;
        $model->load(['user_id = ?', $params['user']], ['limit'=>1]);
        $model->valid() || $this->errorNotFound();
        $app->set('model', $model);
        $app->set('model2', $roles);
        $app->set('roles', (new ViewRbacUsersRoles)->getUserRole($params['user']));
        $app->set('freeRoles', $roles->getFreeUserRole($params['user']));
        $this->breadcrumb->append('detail');
        $this->render('rbac/users/detail.htm');
    }

    public function addRole($app, $params)
    {
        $this->grantWhenHasPermission('create user role');
        $roles = $app->get('POST.roles')?:[];
        $model = new RbacUsersRoles;
        foreach ($roles as $key => $value) {
            $data = [
                'user_id'=>$params['user'],
                'role_id'=>$value,
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

    public function deleteRole($app, $params)
    {
        $this->grantWhenHasPermission('delete user role');
        $model = new RbacUsersRoles;
        $model->findByPK([
            'role_id'=>$params['role'],
            'user_id'=>$params['user'],
            ]);
        $model->erase();
        $this->goBack();
    }

    protected function modelA($id, $required = false)
    {
        $model = new UsersRepository;
        $model->findByPK($id);
        $model->setTTL(0);
        if ($model->dry() && $required) {
            $this->errorNotFound();
        }

        return $model;
    }

    protected function modelB($user_id, $required = false)
    {
        $model = new Profiles;
        $model->load(['user_id = ?', $user_id], ['limit'=>1]);
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
        $this->breadcrumb->append('Users', Nutrition::url('rbacUser'));
        $app->set('menu.active', 'rbac');
        $app->set('can', [
            'insert'=>$this->user->provider->hasPermission('create user'),
            'update'=>$this->user->provider->hasPermission('update user'),
            'delete'=>$this->user->provider->hasPermission('delete user'),
            'read'=>$this->user->provider->hasPermission('read user'),
            'role'=>[
                'insert'=>$this->user->provider->hasPermission('create user role'),
                'update'=>$this->user->provider->hasPermission('update user role'),
                'delete'=>$this->user->provider->hasPermission('delete user role'),
                'read'=>$this->user->provider->hasPermission('read user role'),
            ],
        ]);
    }
}