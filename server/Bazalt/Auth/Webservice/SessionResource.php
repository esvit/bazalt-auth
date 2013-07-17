<?php

namespace Bazalt\Auth\Webservice;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * SessionResource
 *
 * @uri /auth/session
 */
class SessionResource extends \Tonic\Resource
{
    /**
     * Condition method to turn output into JSON
     */
    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";

            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }

    /**
     * @method GET
     * @json
     */
    public function getUser()
    {
        $user = \Bazalt\Auth::getUser();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method POST
     * @json
     */
    public function login()
    {
        $user = null;
        $data = new Validator((array)$this->request->data);
        $data->field('password')->required();
        $data->field('email')->required()->validator('exist_user', function($value) use (&$user, $data) {
            $user = User::getUserByLoginPassword($value, $data->getData('password'), true);
            return ($user != null);
        }, 'User with this email does not exists');

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }
        $user->login($data['remember_me'] == 'true');
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function logout()
    {
        \Bazalt\Auth::logout();
        $user = \Bazalt\Auth::getUser();
        return new Response(Response::OK, $user->toArray());
    }
}