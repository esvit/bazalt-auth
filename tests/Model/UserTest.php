<?php

namespace tests;

use Bazalt\Auth\Model\User;

class ResourceTest extends \tests\BaseCase
{
    protected $model;

    protected function setUp()
    {
        $this->model = User::create();
    }

    protected function tearDown()
    {
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
}