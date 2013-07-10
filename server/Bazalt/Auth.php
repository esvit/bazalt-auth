<?php

namespace Bazalt;

class Auth
{
    protected static $currentUser = null;

    public static function setUser(Auth\Model\User $user, $remember = false)
    {
        if (!$user->is_active) {
            return self::getGuest();
        }
        $session = new \Bazalt\Session('cms');
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