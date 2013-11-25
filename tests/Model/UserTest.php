<?php

namespace tests;

use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;

class ResourceTest extends \Bazalt\Auth\Test\BaseCase
{
    protected $model;

    protected function setUp()
    {
        parent::setUp();

        $this->model = User::create();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->model->id) {
            $this->model->delete();
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

    public function testHasRole()
    {
        // create local role
        $role2 = Role::create();
        $role2->title = 'TestHas';
        $role2->site_id = $this->site->id;
        $role2->save();
        $this->models []= $role2;

        $this->assertFalse($this->user->hasRole($role2->id, $this->site));

        $this->user->Roles->add($role2, ['site_id' => $this->site->id]);

        $this->assertTrue($this->user->hasRole($role2->id, $this->site));
    }

    public function testSwitchRole()
    {
        \Bazalt\Site\Option::set(\Bazalt\Auth::SPLIT_ROLES_OPTION, false);

        // create role
        $role = Role::create();
        $role->title = 'Test1';
        $role->save();
        $this->models []= $role;

        // create role
        $role2 = Role::create();
        $role2->title = 'Test2';
        $role2->save();
        $this->models []= $role2;

        $this->user->Roles->add($role, ['site_id' => $this->site->id]);
        $this->user->Roles->add($role2, ['site_id' => $this->site->id]);

//        print_r($this->user->getRoles());
        $curRole = \Bazalt\Auth::getCurrentRole();
//        print_r($curRole);
        $this->assertEquals($role->id, $curRole->id);

        $this->assertTrue(\Bazalt\Auth::setCurrentRole($role->id));
        $curRole = \Bazalt\Auth::getCurrentRole();
        $this->assertEquals($role->id, $curRole->id);

        $this->assertTrue(\Bazalt\Auth::setCurrentRole($role2->id));
        $curRole = \Bazalt\Auth::getCurrentRole();
        $this->assertEquals($role2->id, $curRole->id);

        $this->assertFalse(\Bazalt\Auth::setCurrentRole(9999));//try to set non exists role

        $curRole = \Bazalt\Auth::getCurrentRole();
        $this->assertEquals($role2->id, $curRole->id);

        \Bazalt\Site\Option::set(\Bazalt\Auth::SPLIT_ROLES_OPTION, true);
    }
}