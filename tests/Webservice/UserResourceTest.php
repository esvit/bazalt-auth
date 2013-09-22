<?php

namespace tests;

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
        $user->save();
        $this->models []= $user;

        $response = new \Bazalt\Rest\Response(403, 'Permission denied');
        $this->assertResponse('DELETE /auth/users/' . $user->id, ['contentType' => 'application/json'], $response);

        $user = User::getById($user->id);
        $this->assertEquals(0, $user->is_deleted);
    }
}