<?php

namespace tests;

use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;

class ResourceTest extends \tests\BaseCase
{
    protected $model;

    protected $models = [];

    protected function setUp()
    {
        $this->model = User::create();
    }

    protected function tearDown()
    {
        if ($this->model->id) {
            $this->model->delete();
        }
        foreach ($this->models as $model) {
            $model->delete();
        }
    }

    public function testGetByIdAndSession()
    {
        $this->model->login = 'test';
        $this->model->save();

        $this->model->updateLastActivity();

        $user = User::getByIdAndSession($this->model->id, \Bazalt\Session::getSessionId());

        $this->assertEquals($this->model->id, $user->id);
    }

    public function testGetUserByLogin()
    {
        $this->model->login = 'test';
        $this->model->save();

        $user = User::getUserByLogin('test');

        $this->assertEquals($this->model->id, $user->id);

        $user = User::getUserByLogin('test', true);

        $this->assertNull($user);

        $this->model->is_active = 1;
        $this->model->save();

        $user = User::getUserByLogin('test', true);

        $this->assertEquals($this->model->id, $user->id);
    }

    public function testGetRoles()
    {
        $this->model->login = 'test';
        $this->model->save();

        // create global role
        $role = Role::create();
        $role->title = 'Test';
        $role->save();
        $this->models []= $role;

        // create site
        $site = \Bazalt\Site\Model\Site::create();
        $site->save();
        $this->models []= $site;

        // create local role
        $role2 = Role::create();
        $role2->title = 'Test2';
        $role2->site_id = $site->id;
        $role2->save();
        $this->models []= $role2;

        $this->model->Roles->add($role, ['site_id' => \Bazalt\Site::getId()]);
        $this->model->Roles->add($role, ['site_id' => $site->id]);
        $this->model->Roles->add($role2, ['site_id' => $site->id]);

        $role = Role::getById($role->id);
        $this->assertEquals([$role], $this->model->getRoles());

        $role2 = Role::getById($role2->id);
        $this->assertEquals([$role, $role2], $this->model->getRoles($site));
    }
}