<?php

namespace Bazalt\Auth\Webservice;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UserResource
 *
 * @uri /user
 */
class UserResource extends \Tonic\Resource
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
        $user = User::getById($_GET['id']);
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
        $user->login();
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method PUT
     * @json
     */
    public function registerUser()
    {
        $data = new Validator((array)$this->request->data);

        // check email
        $data->field('email')->required()->email()->validator('uniqueEmail', function($email) {
            return User::getUserByEmail($email, false) == null;
        }, 'User with this email already exists');

        // check fullname
        $data->field('first')->required();
        $data->field('last')->required();

        // check phone
        $data->field('gender')->required();

        if ($data->validate()) {
            $user = User::create();
            $user->login = $data->getData('email');
            $user->email = $data->getData('email');
            $user->firstname = $data->getData('first');
            $user->lastname = $data->getData('last');
            $user->password = User::cryptPassword($data->getData('password'));
            $user->gender = $data->getData('gender');
            $user->save();

// Create the message
$message = \Swift_Message::newInstance()

  // Give the message a subject
  ->setSubject('Your subject')

  // Set the From address with an associative array
  ->setFrom(array('john@doe.com' => 'John Doe'))

  // Set the To addresses with an associative array
  ->setTo([$user->email])

  // Give it a body
  ->setBody('Here is the message itself')

  // And optionally an alternative body
  ->addPart('<q>Here is the message itself</q>', 'text/html');
  
  $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
  ->setUsername('no-reply@mistinfo.com')
  ->setPassword('gjhndtqy777')
  ;
  $mailer = \Swift_Mailer::newInstance($transport);
  $result = $mailer->send($message);
print_r($result);
        } else {
            return new Response(400, $data->errors());
        }
        return new Response(200, ['valid' => true]);
    }
}