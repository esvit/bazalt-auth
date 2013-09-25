<?php

namespace Bazalt\Auth\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UsersResource
 *
 * @uri /auth/users
 */
class UsersResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getList()
    {
        $users = User::getCollection();
        $users->page((int)$_GET['page']);
        $users->countPerPage((int)$_GET['count']);
        $result = [];
        foreach ($users->fetchPage() as $user) {
            $result []= $user->toArray();
        }
        return new Response(Response::OK,[
            'data' => $result,
            'pager' => [
            'current' => $users->page(),
            'count'   => $users->getPagesCount(),
            'total'   => $users->count(),
            'countPerPage'   => $users->countPerPage()
        ]]);
    }

    /**
     * @method GET
     * @action checkEmail
     * @json
     */
    public function checkEmail()
    {
        if (!isset($_GET['email'])) {
            return new Response(400, ['email' => 'Email can\'t be empty']);
        }
        $user = User::getUserByEmail($_GET['email']);
        return new Response(Response::OK, ['valid' => $user == null]);
    }

    /**
     * @method POST
     * @json
     */
    public function addUser()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('email')->required()->email();

        $user = User::create();

        // check email
        $emailField->validator('uniqueEmail', function($email) {
            return User::getUserByEmail($email, false) == null;
        }, 'User with this email already exists');

        $userRoles = [];
        /*$data->field('roles')->validator('validRoles', function($roles) use (&$userRoles) {
            foreach ($roles as $role) {
                $userRoles[$role] = Role::getById($role);
                if (!$userRoles[$role]) {
                    return false;
                }
            }
            return true;
        }, 'Invalid roles');*/

        $data->field('login')->required();
        $data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->firstname = $data['firstname'];
        $user->secondname = $data['secondname'];
        $user->patronymic = $data['patronymic'];
        $user->password = User::cryptPassword($data['password']);
        $user->gender = $data['gender'];
        $user->save();

        $user->Roles->clearRelations(array_keys($userRoles));
        foreach ($userRoles as $role) {
            $user->Roles->add($role, ['site_id' => 6]);
        }

        // Create the message
        $message = \Swift_Message::newInstance()

            // Give the message a subject
            ->setSubject('Благодарим за регистрацию на MixFree')

            // Set the From address with an associative array
            ->setFrom(array('info@mixjournal.com' => 'MixFree'))

            // Set the To addresses with an associative array
            ->setTo([$user->email])

            // Give it a body
            ->setBody(
                sprintf('Ваш ключ активации: http://localhost/user/activation/%d/%s', $user->id, $user->getActivationKey())
            );

        $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername('no-reply@mistinfo.com')
            ->setPassword('gjhndtqy777')
        ;
        $mailer = \Swift_Mailer::newInstance($transport);
        $result = $mailer->send($message);

        return new Response(200, $user->toArray());
    }
}