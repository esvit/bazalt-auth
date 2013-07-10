<?php

namespace Bazalt\Auth\Model;
use Bazalt\ORM,
    Bazalt\Session;

class Guest extends User
{
    protected $hasName = true;

    public static function getUser($id, $sessionId)
    {
        $user = new Guest();
        $user->id = $user->setting('id') ? $user->setting('id') : $sessionId;
        $user->login = $user->setting('login') ? $user->setting('login') : null;
        $user->firstname = $user->setting('firstname');
        $user->password = $user->setting('password');

        if (empty($user->firstname)) {
            $user->firstname = '';//__('Guest', 'CMS');
            $user->hasName = false;
        }
        if (empty($user->password)) {
            $user->password = CMS\User::generateRandomPassword();
//            $user->setting('password', $user->password);
        }
        $user->password = CMS\User::criptPassword($user->password);
        $user->session_id = $sessionId;

        return $user;
    }

    public function getRolesKey()
    {
        return 'guest_' . parent::getRolesKey();
    }

    public function hasName()
    {
        return $this->hasName;
    }

    public function getRoles()
    {
        return Role::getGuestRoles();
    }

    /**
     * Щоб випадково не зберегли гостя
     */
    public function save()
    {
        throw new \Exception('Can\'t save guest account');
    }

    /**
     * Зберегти гостя як юзера в БД
     */
    public function saveAsUser()
    {
        $user = new User();
        $user->login = $this->login;
        $user->password = $this->password;
        $user->firstname = $this->firstname;
        $user->secondname = $this->secondname;
        $user->patronymic = $this->patronymic;
        $user->email = $this->email;
        $user->gender = $this->gender;
        $user->birth_date = $this->birth_date;
        $user->reg_date = $this->reg_date;
        $user->is_active = false;
        $user->last_activity = $this->last_activity;

        if (!$user->login) {
            $user->login = CMS\User::generateRandomPassword();
        }
        if (!$user->password) {
            $user->password = CMS\User::generateRandomPassword();
        }
        $user->password = CMS\User::criptPassword($user->password);
        $user->save();

        $this->setting('id', $user->id);

        return $user;
    }

    /**
     * Повертає чи встановлює налаштування гостя
     */
    public function setting($name, $value = null, $default = null)
    {
        $session = new Session('guestSetting');
        if ($value !== null) {
            $session->{$settingName} = $value;
        }
        if (isset($session->{$settingName})) {
            return $session->{$settingName};
        }
        return $default;
    }
    
    public function isGuest()
    {
        return true;
    }
}
