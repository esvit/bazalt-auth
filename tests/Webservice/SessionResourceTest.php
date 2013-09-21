<?php

namespace tests;

use Bazalt\Rest;
use Bazalt\Session;
use Tonic;

class SessionResourceTest extends \tests\BaseCase
{
    protected $app;

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
    }

    public function testGet()
    {
        $response = new \Bazalt\Rest\Response(200,[
            'guest_id' => Session::getSessionId(),
            'is_guest' => 1
        ]);
        $this->assertResponse('GET /auth/session', [], $response);
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

        $user = \Bazalt\Auth\Model\User::getById(1);
        $response = new \Bazalt\Rest\Response(200, $user->toArray());

        $this->assertResponse('POST /auth/session', [
            'data' => json_encode(array(
                'email' => 'admin',
                'password' => '1'
            ))
        ], $response);

        // get logined user
        $response = new \Bazalt\Rest\Response(200, $user->toArray());
        $this->assertResponse('GET /auth/session', [], $response);

        // logout
        $response = new \Bazalt\Rest\Response(200, true);
        $this->assertResponse('DELETE /auth/session', [], $response);

        // guest logout
        $response = new \Bazalt\Rest\Response(200, false);
        $this->assertResponse('DELETE /auth/session', [], $response);
    }
}