<?php

namespace Bazalt\Auth\Test;

use Bazalt\Rest;

abstract class BaseCase extends \Bazalt\Site\Test\BaseCase
{
    protected $user = null;

    protected $models = [];

    protected function setUp()
    {
        parent::setUp();

        $user = \Bazalt\Auth\Model\User::getUserByLogin('__Test__');
        if ($user) {
            $user->delete();
        }

        $this->user = \Bazalt\Auth\Model\User::create();
        $this->user->login = '__Test__';
        $this->user->is_active = 1;
        $this->user->save();

        \Bazalt\Auth::setUser($this->user);
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