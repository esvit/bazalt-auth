<?php

namespace tests;

use Bazalt\Rest;
use Bazalt\Session;
use Tonic;

class ResourceTest extends \tests\BaseCase
{
    protected $app;

    protected function setUp()
    {
        $config = array(
            'load' => array(
                __DIR__ .'/../server/Bazalt/Auth/Webservice/*.php'
            )
        );
        $this->app = new Tonic\Application($config);
    }

    protected function tearDown()
    {
    }

    public function testGet()
    {
        $request = new Tonic\Request(array(
            'uri' => '/auth/session'
        ));

        $response = new \Bazalt\Rest\Response(200,[
            'guest_id' => Session::getSessionId(),
            'is_guest' => 1
        ]);

        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);

        $this->assertResponse($resource, $response);
    }


    public function testPost()
    {
        $request = new Tonic\Request(array(
            'uri' => '/auth/session',
            'method' => 'POST',
            'contentType' => 'application/json',
            'data' => json_encode(array(
                'hello' => 'computer'
            ))
        ));
        $response = new \Bazalt\Rest\Response(400, [
            'password' => [
                'required' => 'Field cannot be empty'
            ],
            'email' => [
                'required' => 'Field cannot be empty',
                'exist_user' => 'User with this email does not exists'
            ]
        ]);

        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);

        $this->assertResponse($resource, $response);

        $request = new Tonic\Request(array(
            'uri' => '/auth/session',
            'method' => 'POST',
            'contentType' => 'application/json',
            'data' => json_encode(array(
                'email' => 'admin',
                'password' => '1'
            ))
        ));

        $user = \Bazalt\Auth\Model\User::getById(1);
        $response = new \Bazalt\Rest\Response(200, $user->toArray());

        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);

        $this->assertResponse($resource, $response);

        // get logined user
        $request = new Tonic\Request(array(
            'uri' => '/auth/session'
        ));
        $response = new \Bazalt\Rest\Response(200, $user->toArray());
        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);
        $this->assertResponse($resource, $response);

        // logout
        $request = new Tonic\Request(array(
            'uri' => '/auth/session',
            'method' => 'DELETE'
        ));
        $response = new \Bazalt\Rest\Response(200, true);
        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);
        $this->assertResponse($resource, $response);

        // guest logout
        $request = new Tonic\Request(array(
            'uri' => '/auth/session',
            'method' => 'DELETE'
        ));
        $response = new \Bazalt\Rest\Response(200, false);
        $resource = new \Bazalt\Auth\Webservice\SessionResource($this->app, $request);
        $this->assertResponse($resource, $response);
    }
}