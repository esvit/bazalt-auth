<?php

namespace Bazalt\Auth\Test;

use Bazalt\Rest;

abstract class BaseCase extends \Bazalt\Site\Test\BaseCase
{
    protected $user = null;

    protected $app;

    protected $models = [];

    protected function initApp($files)
    {
        $config = array(
            'load' => $files
        );
        $this->app = new \Tonic\Application($config);
    }

    protected function setUp()
    {
        parent::setUp();

        $user = \Bazalt\Auth\Model\User::getUserByLogin('__Test__');
        if ($user) {
            $user->delete();
        }

        $this->user = \Bazalt\Auth\Model\User::create();
        $this->user->login = '__Test__';
        $this->user->email = 'test@equalteam.net';
        $this->user->is_active = 1;
        $this->user->password = \Bazalt\Auth\Model\User::cryptPassword(1);
        $this->user->save();

        \Bazalt\Auth::setUser($this->user);
    }

    protected function addPermission($permName, $user = null, $site = null)
    {
        if($user == null) {
            $user = $this->user;
        }
        if($site == null) {
            $site = $this->site;
        }

        $role =  \Bazalt\Auth\Model\Role::create();
        $role->title = $permName.' test role';
        $role->save();
        $this->models []= $role;

        $perm = \Bazalt\Auth\Model\Permission::getById($permName);
        if(!$perm) {
            $perm = new \Bazalt\Auth\Model\Permission();
            $perm->id = $permName;
            $perm->save();
            $this->models []= $perm;
        }
        $role->Permissions->add($perm);

        $user->Roles->add($role, ['site_id' => $site->id]);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->user->id) {
            $this->user->delete();
        }
        $this->user = null;
        \Bazalt\Auth::setUser(null);

        foreach ($this->models as $model) {
            $model->delete();
        }
    }
}