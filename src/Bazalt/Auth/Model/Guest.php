<?php

namespace Bazalt\Auth\Model;
use Bazalt\ORM,
    Bazalt\Session;

class Guest extends \stdClass
{
    public static function create($sessionId)
    {
        $user = new Guest();
        $user->guest_id = $sessionId;
        $user->is_guest = true;

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
            $session->{$name} = $value;
        }
        if (isset($session->{$name})) {
            return $session->{$name};
        }
        return $default;
    }
    
    public function isGuest()
    {
        return true;
    }

    public function hasPermission()
    {
        return false;
    }

    public function toArray()
    {
        $arr = (array)$this;
        $arr['roles'] = [];
        $arr['acl'] = [];

        return $arr;
    }
}