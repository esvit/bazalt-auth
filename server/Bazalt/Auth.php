<?php

namespace Bazalt;

class Auth
{
    const SPLIT_ROLES_OPTION = 'Auth.SplitRoles';

    /**
     * @var \Bazalt\Auth\Model\User
     */
    protected static $currentUser = null;

    /**
     * Return current user
     *
     * @return Auth\Model\Guest|Auth\Model\User
     */
    public static function getUser()
    {
        $session = new Session('auth');

        if (!self::$currentUser && $session->user_id) {
            $user = Auth\Model\User::getByIdAndSession((int)$session->user_id, Session::getSessionId());

            if ($user && isset($_COOKIE['authorization_token']) && ($_COOKIE['authorization_token'] == $session->authorization_token)) {
                self::$currentUser = $user;
            } else {
                self::logout();
            }
            if (self::$currentUser) {
                self::$currentUser->updateLastActivity();
                /*$timezone = self::$currentUser->setting(Auth\Model\User::TIME_ZONE_SETTING, null);
                if($timezone) {
                    @date_default_timezone_set($timezone);
                }*/
            }
        }

        if (!self::$currentUser) {
            self::$currentUser = Auth\Model\Guest::create(Session::getSessionId());
        }
        return self::$currentUser;
    }

    public static function setUser($user, $remember = false)
    {
        if (!$user || !$user->is_active) {
            return Auth\Model\Guest::create(Session::getSessionId());
        }
        $session = new Session('auth');
        Session::regenerateSessionId();
        self::$currentUser = $user;

        /* if (!$user->hasRight(null, Bazalt::ACL_CAN_LOGIN)) {
             self::$currentUser = null;
             return null;
         }*/

        $user->session_id = Session::getSessionId();
        $user->updateLastActivity();

        $token = $user->getAuthorizationToken();
        $session->user_id = $user->id;
        $session->authorization_token = $token;

        $lifetime = $remember ? (time() + self::getUserSessionLifetime()) : 0;

        $_COOKIE['authorization_token'] = $token;

        if (!TESTING_STAGE) {
            setcookie('authorization_token', $_COOKIE['authorization_token'], $lifetime, '/', null, false, true);
        }
        return $user;
    }

    public static function logout()
    {
        $_COOKIE['authorization_token'] = null;
        if (!TESTING_STAGE) {
            setcookie('authorization_token', '', time() - 3600, '/', null, false, false);
        }

        $session = new Session('auth');
        self::$currentUser = null;
        unset($session->user_id);
        unset($session->authorization_token);
        unset($session->currentRoleId);
        $session->destroy();
        Session::regenerateSessionId();
    }

    public static function getUserSessionLifetime()
    {
        // 30 days
        return 30 * 24 * 60 * 60;
    }


    /**
     * Only for separated roles mode
     *
     * @return \Bazalt\Auth\Model\Role|null
     */
    public static function getCurrentRole()
    {
        $user = self::getUser();
        if($user->isGuest()) {
            return null;
        }
        $session = new Session('auth');
        if (isset($session->currentRoleId)) {
            $curRole = \Bazalt\Auth\Model\Role::getById((int)$session->currentRoleId);
            if(!$curRole) {
                return null;
            }
            return self::getUser()->hasRole($curRole->id) ? $curRole : null;
        }
        $q = $user->Roles->getQuery();
        $q->andWhere('ref.site_id = ?', \Bazalt\Site::getId());
        $q->limit(1);
        return $q->fetch();
    }

    /**
     * Only for separated roles mode
     *
     * @return bool true if set role - success
     */
    public static function setCurrentRole($roleId)
    {
        $session = new Session('auth');
        $curRole = \Bazalt\Auth\Model\Role::getById((int)$roleId);
        if($curRole) {
            $user = self::getUser();
            if($user->is_god || $user->hasRole($curRole->id)) {
                $session->currentRoleId = $curRole->id;
                return true;
            }
        }
        return false;
    }
}