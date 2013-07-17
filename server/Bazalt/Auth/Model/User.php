<?php

namespace Bazalt\Auth\Model;

use Bazalt\ORM;
use Framework\System\Locale\Format;

class User extends Base\User
{
    const TIME_ZONE_SETTING = 'timezone';

    protected $systemAcl = null;

    protected $componentsAcl = array();
    
    protected $timeOffset = null;

    public static function create()
    {
        $user = new User();
        $user->gender = 'unknown';

        return $user;
    }

    public function hasName()
    {
        return true;
    }

    public function getTimeOffset()
    {
        if(isset($this->timeOffset) && $this->timeOffset !== null) {
            return $this->timeOffset;
        }
        $this->timeOffset = strtotime(gmdate('Y-m-d H:i:s')) - strtotime(date('Y-m-d H:i:s'));
        return $this->timeOffset;
    }

    public function formatDate($format, $timestamp = null)
    {
        if($timestamp === null) {
            $timestamp = time();
        }
        $ts = $timestamp - $this->getTimeOffset();
        return Format::formatDate($format, $ts);
    }

    /**
     * Get user by id and session id
     */
    public static function getByIdAndSession($id, $sessionId = null)
    {
        $q = ORM::select('Bazalt\Auth\Model\User u');
                //->leftJoin('Bazalt\Auth\Model\SiteRefUser ref', array('user_id', 'u.id'))
                //->where('ref.user_id = ?', $id)
                //->andWhere('ref.site_id = ?', CMS\Bazalt::getSiteID());

        if ($sessionId != null) {
            //$q->andWhere('ref.session_id = ?', $sessionId);
        }
        return $q->noCache()->fetch();
    }

    public function getAuthorizationToken()
    {
        $sid = \Bazalt\Session::getSessionId();
        return md5($this->id . $sid . time());
    }

    /**
     * Get access bit mask for component or system
     */
    protected function getRoleBitmask($component = null)
    {
        if ($component == null) {
            if ($this->systemAcl === null) {
                $res = 0;
                foreach ($this->getRoles() as $role) {
                    $res |= $role->system_acl;
                }
                $this->systemAcl = $res;
            }
            return $this->systemAcl;
        } else if (!isset($this->componentsAcl[$component->id])) {
            $roles = array();
            foreach ($this->getRoles() as $role) {
                $roles []= $role->id;
            }

            // no roles - no rights
            if (count($roles) == 0) {
                return 0;
            }

            $acl = Role::getBitmask($roles, $component);
            $this->componentsAcl[$component->id] = $acl;
        }
        return $this->componentsAcl[$component->id];
    }
    
    public function getRoles()
    {
        $splitRoles = CMS\Option::get(CMS\User::SPLIT_ROLES_OPTION, true);
        if ($splitRoles) {
            $q = $this->Roles->getQuery();
            $q->andWhere('ref.site_id = ?', CMS\Bazalt::getSiteId());
            return $q->fetchAll();
        } else {
            $roles = Role::getGuestRoles();
            $currentRole = CMS\User::getCurrentRole();
            if($currentRole) {
                $roles []= $currentRole;
            }
            return $roles;
        }
    }

    /**
     * Check if user has rights
     */
    public function hasRight($component = null, $roleValue)
    {
        if ($this->is_god || ($component != null && $this->hasRight(null, CMS\Bazalt::ACL_GODMODE))) {
            return true;
        }
        if ($component != null && !($component instanceof Component)) {
            $component = Component::getComponent(is_object($component) ? get_class($component) : $component);
        }
        return ($roleValue & $this->getRoleBitmask($component)) != 0;
    }

    /**
     * Повертає чи встановлює налаштування користувача
     */
    public function setting($name, $value = null, $default = null)
    {
        $setting = UserSetting::getUserSetting($this, $name);
        if (!$setting && $value === null) {
            return $default;
        }
        if ($value !== null) {
            if (!$setting) {
                $setting = UserSetting::create($this, $name);
            }
            $setting->value = $value;
            $setting->save();
        }
        return $setting->value;
    }

    public static function getUserWithSetting($settingName, $settingValue, $active = null)
    {
        $q = ORM::select('Bazalt\Auth\Model\User u')
                ->innerJoin('Bazalt\Auth\Model\UserSetting ref', array('user_id', 'u.id'))
                ->where('ref.setting = ?', $settingName)
                ->andWhere('ref.value = ?', $settingValue);

        if ($active !== null) {
            $q->andWhere('u.is_active = ?', $active ? 1 : 0);
        }
        return $q->fetchAll();
    }

    public function toArray()
    {
        $ret = $this->values;
        unset($ret['password']);
        unset($ret['session_id']);
        unset($ret['is_god']);
        $ret['roles'] = array();
        foreach ($this->Roles as $role) {
            $ret['roles'][] = $role->id;
        }
        $ret['fullname'] = $this->getName();
        return $ret;
    }

    public static function getUserByLogin($login, $onlyPublish = false)
    {
        $q = ORM::select('Bazalt\Auth\Model\User u')
                ->where('login = ?', $login);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getUserByLoginPassword($login, $password, $checkMail = false)
    {
        $password = self::cryptPassword($password);
        $q = ORM::select('Bazalt\Auth\Model\User u')
                ->where('login = ?', $login)
                ->andWhere('password = ?', $password)
                ->andWhere('is_active = ?', 1);
        $user = $q->fetch();
        if (!$user && $checkMail) {
            $q = ORM::select('Bazalt\Auth\Model\User u')
                    ->where('email = ?', $login)
                    ->andWhere('password = ?', $password)
                    ->andWhere('is_active = ?', 1);
            $user = $q->fetch();
        }
        return $user;
    }
    
    public static function getUserByEmail($email, $onlyPublish = false)
    {
        $q = User::select()
                 ->where('email = ?', $email);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getByLoginAndEmail($login, $email, $onlyPublish = false)
    {
        $q = User::select()
                 ->where('login = ?', $login)
                 ->andWhere('email = ?', $email);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getUsersCountFromDate($date = null)
    {
        $q = User::select();
        if ($date != null) {
            $q->where('reg_date > FROM_UNIXTIME(?)', $date);
        }
        return $q->exec();
    }

    public function getActivationKey()
    {
        return md5($this->login . $this->password . CMS_Bazalt::getSecretKey());
    }

    public function getRemindKey()
    {
        return md5($this->login . $this->email . CMS_Bazalt::getSecretKey());
    }

    /**
     * remove user setting
     */
    public function removeSetting($name)
    {
        UserSetting::removeUserSetting($this, $name);
    }

    public function setPhoto($filename)
    {
        $this->setting('photo', $filename);
    }

    public function getPhoto($size = null)
    {
        $photo = $this->setting('photo');

        if (!empty($photo) && $size != null) {
            return CMS_Image::getThumb($photo, $size);
        }
        return $photo;
    }

    public function setAvatar($filename)
    {
        $this->setting('avatar', $filename);
    }
    
    public function getAvatar($size = null)
    {
        $avatar = $this->setting('avatar');
        if (empty($avatar)) {
            $avatar = '/uploads/default_avatar.jpg';
            $this->setting('avatar', $avatar);
        }

        if (!empty($avatar) && $size != null) {
            return CMS_Image::getThumb($avatar, $size);
        }
        return $avatar;
    }

    public function getName()
    {
        $name = trim($this->secondname . ' ' . $this->firstname);
        if (empty($name)) {
            $name = $this->login;
        }
        return $name;
    }
    
    public static function getOnlineUsers()
    {
        $p = (int)CMS\Option::get(CMS_Bazalt::ONLINEPERIOD_OPTION, 5);
        $q = User::select()
            ->where('last_activity BETWEEN ? AND ?', array(
                date('Y-m-d H:i:s', strtotime('now -' . $p . ' minutes')),
                date('Y-m-d H:i:s', strtotime('now +' . $p . ' minutes'))
            ))
            ->noCache();
        return $q->fetchAll();
    }
    
    public function login($remember = false)
    {
        if (!$this->isGuest()) {
            \Bazalt\Auth::setUser($this, $remember);
        }
    }

    public function updateLastActivity($time = null)
    {
        if ($time == null) {
            $time = time();
        }
        /*$q = SiteRefUser::select()
                ->where('user_id = ?', $this->id)
                ->andWhere('site_id = ?', CMS\Bazalt::getSiteID());

        $activity = $q->fetch();
        if (!$activity) {
            $activity = new SiteRefUser();
            $activity->site_id = CMS\Bazalt::getSiteID();
            $activity->user_id = $this->id;
        }
        $activity->session_id = \Framework\System\Session\Session::getSessionId();
        $activity->last_activity = date('Y-m-d H:i:s', $time);
        $activity->save();*/
        
        ORM::update('Bazalt\Auth\Model\User')
           ->set('last_activity', date('Y-m-d H:i:s', $time))
           ->where('id = ?', $this->id)
           ->autoClearCache(false)
           ->exec(false);
    }

    public static function deleteByIds($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        $q = ORM::delete('Bazalt\Auth\Model\User a')
                ->whereIn('a.id', $ids)
                ->andWhere('a.id <> ?', CMS_User::getUser()->id);

        return $q->exec();
    }

    public function isGuest()
    {
        return false;
    }

    public static function getCollection()
    {
        $q = ORM::select('Bazalt\Auth\Model\User f')
                ->orderBy('is_active ASC');
        return new \Bazalt\ORM\Collection($q);
    }

    public function addAfterLoginRole()
    {
        $role = CMS_Option::get(CMS\User::LOGIN_USER_ROLE_OPTION, false);
        if (is_numeric($role)) {
            $role = Role::getById($role);
            if ($role) {
                $this->Roles->add($role, array('site_id' => CMS_Bazalt::getSiteId()));
            }
        }
    }
    
    public static function cryptPassword($password)
    {
        return hash('sha512', $password);
    }

    public static function generateRandomPassword($length = 6, $symbols = 'abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPRSTUVXYZ1234567890')
    {
        $symbolsArray = str_split($symbols, 1);
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $symbolsArray[rand(0, count($symbolsArray) - 1)];
        }
        return $password;
    }
}
