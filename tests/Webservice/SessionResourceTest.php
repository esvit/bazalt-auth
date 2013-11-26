<?php

namespace tests;

use Bazalt\Rest;
use Bazalt\Session;
use Tonic;

class SessionResourceTest extends \Bazalt\Auth\Test\BaseCase
{
    protected $app;

    protected function setUp()
    {
        parent::setUp();

        $config = array(
            'load' => array(
                __DIR__ .'/../../server/Bazalt/Auth/Webservice/*.php'
            )
        );
        $this->app = new Tonic\Application($config);
    }

    public function testGet()
    {
        \Bazalt\Auth::logout();
        $response = new \Bazalt\Rest\Response(200,[
            'guest_id' => Session::getSessionId(),
            'is_guest' => 1,
            'roles' => [],
            'acl' => []
        ]);
        $this->assertResponse('GET /auth/session', ['contentType' => 'application/json'], $response);
    }


    public function testPost()
    {
        $response = new \Bazalt\Rest\Response(400, [
            'password' => [
                'required' => 'Field cannot be empty'
            ],
            'email' => [
                'required' => 'Field cannot be empty',
                'exist_user' => 'User with this login/email does not exists'
            ]
        ]);

        $this->assertResponse('POST /auth/session', [
            'data' => json_encode(array(
                'hello' => 'computer'
            ))
        ], $response);

        $user = \Bazalt\Auth\Model\User::getById($this->user->id);
        $response = new \Bazalt\Rest\Response(200, $user->toArray());

        $this->assertResponse('POST /auth/session', [
            'data' => json_encode(array(
                'email' => $this->user->login,
                'password' => '1'
            ))
        ], $response);

        // get logined user
        $response = new \Bazalt\Rest\Response(200, $user->toArray());
        $this->assertResponse('GET /auth/session', ['contentType' => 'application/json'], $response);

        // logout
        $response = new \Bazalt\Rest\Response(200, '/is_guest/');
        $this->assertRegExpResponse('DELETE /auth/session', [], $response);

        // guest logout
        $response = new \Bazalt\Rest\Response(200, '/is_guest/');
        $this->assertRegExpResponse('DELETE /auth/session', [], $response);
    }
}