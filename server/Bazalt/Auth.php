<?php

namespace Bazalt;

class Auth
{
    const ACL_GUEST = 1;

    const ACL_CAN_LOGIN = 2;

    const ACL_CAN_MANAGE = 4;

    /**
     * @var \Bazalt\Auth\Model\User
     */
    protected static $currentUser = null;

    protected static $containers = [];

    public static function registerContainers($containers)
    {
        self::$containers = $containers;
    }

    public static function getUserLevels(Auth\Model\User $user)
    {
        $roles = $user->getRoles();
        $levels = [
            'system' => $user->isGuest() ? \Bazalt\Auth::ACL_GUEST : \Bazalt\Auth::ACL_CAN_LOGIN
        ];
        foreach ($roles as $role) {
            $levels['system'] |= $role->system_acl;
        }
        foreach (self::$containers as $name => $container) {
            $container->getUserLevels($user, $levels);
        }
        return $levels;
    }

    public static function getAclLevels()
    {
        $levels = [
            'system' => [
                'guest' => self::ACL_GUEST,
                'can_login' => self::ACL_CAN_LOGIN,
                'can_manage' => self::ACL_CAN_MANAGE
            ]
        ];
        foreach (self::$containers as $name => $container) {
            $levels[$name] = $container->getAclLevels();
        }
        return $levels;
    }

    public static function getUser()
    {
        $session = new \Bazalt\Session('auth');

        if (!self::$currentUser && $session->cmsUser) {
            $user = Auth\Model\User::getByIdAndSession((int)$session->cmsUser, $session->getSessionId());

            if ($user && ($_COOKIE['authorization_token'] == $session->authorization_token)) {
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
            self::$currentUser = self::getGuest();
        }
        return self::$currentUser;
    }

    public static function getGuest()
    {
        $session = new \Bazalt\Session('auth');
        if (isset($_COOKIE['GuestId'])) {
            $guestId = $_COOKIE['GuestId'];
        } else {
            $guestId = $session->getSessionId();
            self::setGuestId($guestId);
        }

        $guest = Auth\Model\Guest::getUser($guestId, $session->getSessionId());
        return $guest;
    }

    public static function setUser(Auth\Model\User $user, $remember = false)
    {
        if (!$user->is_active) {
            return self::getGuest();
        }
        $session = new \Bazalt\Session('auth');
        $session->regenerateSessionId();
        self::$currentUser = $user;

        /* if (!$user->hasRight(null, Bazalt::ACL_CAN_LOGIN)) {
             self::$currentUser = null;
             return null;
         }*/

        $user->session_id = $session->getSessionId();
        $user->updateLastActivity();

        self::setGuestId($user->session_id);

        $token = $user->getAuthorizationToken();
        $session->cmsUser = $user->id;
        $session->authorization_token = $token;

        $lifetime = $remember ? (time() + self::getUserSessionLifetime()) : 0;

        $_COOKIE['authorization_token'] = $token;

        setcookie('authorization_token', $_COOKIE['authorization_token'], $lifetime, '/', null, false, true);
        return $user;
    }

    public static function logout()
    {
        $_COOKIE['authorization_token'] = null;
        setcookie('authorization_token', '', time() - 3600, '/', null, false, false);

        $session = new \Bazalt\Session('cms');
        self::$currentUser = null;
        unset($session->cmsUser);
        unset($session->authorization_token);
        unset($session->currentRoleId);
        $session->destroy();
        $session->regenerateSessionId();
    }

    protected static function setGuestId($guestId)
    {
        $_COOKIE['GuestId'] = $guestId;
        setcookie('GuestId', $guestId, (time() + self::getUserSessionLifetime()), '/', null, false, false);
    }

    public static function getUserSessionLifetime()
    {
        // 30 days
        return 30 * 24 * 60 * 60;
    }
}