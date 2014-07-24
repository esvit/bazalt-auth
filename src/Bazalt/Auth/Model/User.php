<?php

namespace Bazalt\Auth\Model;

use Bazalt\ORM;
use Framework\System\Locale\Format;

class User extends Base\User
{
    const TIME_ZONE_SETTING = 'timezone';

    protected $systemAcl = null;

    protected $levels = [];

    protected $_settings = [];

    protected $timeOffset = null;

    /**
     * Создает пустого пользователя без сохранения его в базу
     *
     * @return User
     */
    public static function create()
    {
        $user = new User();
        $user->password = '';
        $user->gender = 'unknown';
        $user->is_deleted = 0;
        $user->need_edit = 1;

        return $user;
    }

    public function levels($levels = null)
    {
        if ($levels !== null) {
            $this->levels = $levels;
            return $this;
        }
        return $this->levels;
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
        $q = ORM::select('Bazalt\\Auth\\Model\\User u')
                ->innerJoin('Bazalt\\Auth\\Model\\SiteRefUser ref', ['user_id', 'u.id'])
                ->where('u.id = ?', $id)
                ->andWhere('ref.site_id = ?', \Bazalt\Site::getId());

        if ($sessionId != null) {
            $q->andWhere('ref.session_id = ?', $sessionId);
        }
        return $q->noCache()->fetch();
    }

    public function getAuthorizationToken()
    {
        $sid = \Bazalt\Session::getSessionId();
        return md5($this->id . $sid . time());
    }

    public function hasRole($roleId, $site = null)
    {
        $site = ($site) ? $site : \Bazalt\Site::get();

        //get all sites roles
        $ql = ORM::select('Bazalt\\Auth\\Model\\RoleRefUser ru', 'COUNT(*) as cnt')
          ->andWhere('ru.user_id = ?', $this->id)
          ->andWhere('ru.site_id = ?', $site->id)
          ->andWhere('ru.role_id = ?', $roleId);

        //get all global roles
        $qg = ORM::select('Bazalt\\Auth\\Model\\RoleRefUser ru', 'COUNT(*) as cnt')
            ->innerJoin('Bazalt\\Auth\\Model\\Role r', ['id', 'ru.role_id'])
            ->where('r.site_id IS NULL')
            ->andWhere('ru.user_id = ?', $this->id)
            ->andWhere('ru.role_id = ?', $roleId);

        return (int)$ql->fetch('stdClass')->cnt > 0 || (int)$qg->fetch('stdClass')->cnt > 0;
    }

    public function getRoles($site = null)
    {
        if($this->is_god) {
            $q = ORM::select('Bazalt\\Auth\\Model\\Role r', 'r.*')
                    ->groupBy('r.id');
            return $q->fetchAll();
        }
        $site = ($site) ? $site : \Bazalt\Site::get();

        //get all sites roles
        $ql = ORM::select('Bazalt\\Auth\\Model\\Role r', 'r.*')
            ->innerJoin('Bazalt\\Auth\\Model\\RoleRefUser ru', ['role_id', 'r.id'])
            ->andWhere('ru.user_id = ?', $this->id)
            ->andWhere('ru.site_id = ?', $site->id)
            ->andWhere('r.site_id = ?', $site->id);


        //get all global roles
        $qg = ORM::select('Bazalt\\Auth\\Model\\Role r', 'r.*')
            ->innerJoin('Bazalt\\Auth\\Model\\RoleRefUser ru', ['role_id', 'r.id'])
            ->where('r.site_id IS NULL')
            ->andWhere('ru.user_id = ?', $this->id);

        $q = ORM::union($ql, $qg);
        return $q->fetchAll('Bazalt\\Auth\\Model\\Role');
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission, $site = null)
    {
        $site = (!$site) ? \Bazalt\Site::get() : $site;
        if ($this->is_god) {
            return true;
        }
        return in_array($permission, $this->getPermissions($site));
    }

    /**
     * Возвращает или устанавливает настройки пользователя, зависит от количества параметров
     */
    public function setting($name, $value = null, $default = null)
    {
        if (isset($this->_settings[$name]) && $value === null) {
            return $this->_settings[$name];
        }

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
        $this->_settings[$name] = $setting->value;
        return $setting->value;
    }

    public static function getUserWithSetting($settingName, $settingValue, $active = null)
    {
        $q = ORM::select('Bazalt\\Auth\\Model\\User u')
                ->innerJoin('Bazalt\\Auth\\Model\\UserSetting ref', array('user_id', 'u.id'))
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
        unset($ret['is_god']);
        unset($ret['session_id']);

        $ret['fullname'] = $this->getName();
        $ret['age'] = $this->getAge();
        $ret['birth_date'] = date('d.m.Y', strToTime($this->birth_date));
        $ret['is_active'] = $this->is_active == '1';
        //if ($this->is_deleted) {
            $ret['is_deleted'] = $this->is_deleted == '1';
        //}
        $ret['permissions'] = $this->getPermissions();
        return $ret;
    }

    /**
     * Возвращает пользователя по логину, если такой есть в базе
     *
     * @param $login
     * @param bool $onlyPublish
     * @return User|null
     */
    public static function getUserByLogin($login, $onlyPublish = false)
    {
        $q = ORM::select('Bazalt\\Auth\\Model\\User u')
            ->where('is_deleted = 0')
            ->andWhere('login = ?', $login);

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
                ->andWhere('is_active = ?', 1)
                ->andWhere('is_deleted = ?', 0);
        $user = $q->fetch();
        if (!$user && $checkMail) {
            $q = ORM::select('Bazalt\Auth\Model\User u')
                    ->where('email = ?', $login)
                    ->andWhere('password = ?', $password)
                    ->andWhere('is_active = ?', 1)
                    ->andWhere('is_deleted = ?', 0);
            $user = $q->fetch();
        }
        return $user;
    }
    
    public static function getUserByEmail($email, $onlyPublish = false)
    {
        $q = User::select()
                ->where('is_deleted = 0')
                ->andWhere('email = ?', $email);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getByLoginAndEmail($login, $email, $onlyPublish = false)
    {
        $q = User::select()
                 ->andWhere('is_deleted = 0')
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
        return md5($this->login . $this->password . $this->created_at);// . \Bazalt\Site::getSecretKey());
    }

    public function getRemindKey()
    {
        return md5($this->login . $this->email);// . CMS_Bazalt::getSecretKey());
    }

    public function getPermissions($site = null)
    {
        $site = ($site) ? $site : \Bazalt\Site::get();
        $splitRoles = \Bazalt\Site\Option::get(\Bazalt\Auth::SPLIT_ROLES_OPTION, true);

        $ret = [];
        if($this->is_god) {
            $q = ORM::select('Bazalt\\Auth\\Model\\Permission p', 'p.id');
            $res = $q->fetchAll();
            foreach ($res as $perm) {
                $ret []= $perm->id;
            }
        } else {
            if($splitRoles) {
                $q = ORM::select('Bazalt\\Auth\\Model\\Permission p', 'p.id')
                    ->innerJoin('Bazalt\\Auth\\Model\\RoleRefPermission rp', ['permission_id', 'p.id'])
                    ->innerJoin('Bazalt\\Auth\\Model\\RoleRefUser ru', ['role_id', 'rp.role_id'])
                    ->where('ru.user_id = ?', $this->id);
                $res = $q->fetchAll();
                foreach ($res as $perm) {
                    $ret []= $perm->id;
                }
            } else {
                $roles = Role::getGuestRoles();
                if(!$this->isGuest()) {
                    $currentRole = \Bazalt\Auth::getCurrentRole();
                    if($currentRole) {
                        $roles = [
                            $currentRole
                        ];
                    }
                }
                foreach($roles as $role) {
                    $res = $role->getPermissions();
                    foreach ($res as $perm) {
                        $ret[$perm->id] = $perm->id;
                    }
                }
            }
        }

        $ret = array_values($ret);
        if(!$this->isGuest()) {
            $ret []= 'auth.user_logged';
        }
        return $ret;
    }

    /**
     * remove user setting
     */
    public function removeSetting($name)
    {
        UserSetting::removeUserSetting($this, $name);
    }

    public function getName()
    {
        $name = trim($this->secondname . ' ' . $this->firstname);
        if (empty($name)) {
            $name = $this->login;
        }
        return $name;
    }

    public function getAge()
    {
        $date = new \DateTime($this->birth_date);
        $now = new \DateTime();
        $interval = $now->diff($date);
        return $interval->y;
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
        $q = SiteRefUser::select()
                ->where('user_id = ?', $this->id)
                ->andWhere('site_id = ?', \Bazalt\Site::getId());

        $activity = $q->fetch();
        if (!$activity) {
            $activity = new SiteRefUser();
            $activity->site_id = \Bazalt\Site::getId();
            $activity->user_id = $this->id;
        }
        $activity->session_id = \Bazalt\Session::getSessionId();
        $activity->last_activity = date('Y-m-d H:i:s', $time);
        $activity->save();

        ORM::update('Bazalt\\Auth\\Model\\User')
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
                ->where('is_deleted = 0')
                ->orderBy('is_active ASC');

        return new \Bazalt\ORM\Collection($q);
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
