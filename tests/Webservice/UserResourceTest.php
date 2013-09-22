<?php

namespace tests;

use Bazalt\Auth\Model\Permission;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Rest;
use Bazalt\Session;
use Tonic;

class UserResourceTest extends \tests\BaseCase
{
    protected $app;

    protected $models = [];

    protected function setUp()
    {
        $config = array(
            'load' => array(
                __DIR__ .'/../../server/Bazalt/Auth/Webservice/*.php'
            )
        );
        $this->app = new Tonic\Application($config);
    }

    protected function tearDown()
    {
        foreach ($this->models as $model) {
            $model->delete();
        }
        $this->models = [];
    }

    public function testDelete()
    {
        $user = User::create();
        $user->login = 'test';
        $user->is_active = true;
        $user->save();
        $this->models []= $user;

        $response = new \Bazalt\Rest\Response(403, 'Permission denied');
        $this->assertResponse('DELETE /auth/users/' . $user->id, ['contentType' => 'application/json'], $response);

        $user = User::getById($user->id);
        $this->assertEquals(0, $user->is_deleted);

        $role = Role::create();
        $role->title = 'test';
        $role->save();
        $this->models []= $role;

        $role->Permissions->add(Permission::getById('auth.can_delete_user'));

        $user->Roles->add($role, ['site_id' => \Bazalt\Site::getId()]);

        // login
        \Bazalt\Auth::setUser($user);

        $response = new \Bazalt\Rest\Response(400, ['id' => 'Can\'t delete yourself']);
        $this->assertResponse('DELETE /auth/users/' . $user->id, ['contentType' => 'application/json'], $response);

        $user = User::getById($user->id);
        $this->assertEquals(0, $user->is_deleted);

        $user2 = User::create();
        $user2->login = 'test2';
        $user2->is_active = true;
        $user2->save();
        $this->models []= $user2;

        $user2->Roles->add($role, ['site_id' => \Bazalt\Site::getId()]);

        // login
        \Bazalt\Auth::setUser($user2);

        $response = new \Bazalt\Rest\Response(200, true);
        $this->assertResponse('DELETE /auth/users/' . $user->id, ['contentType' => 'application/json'], $response);

        $user = User::getById($user->id);
        $this->assertEquals(1, $user->is_deleted);
    }
}